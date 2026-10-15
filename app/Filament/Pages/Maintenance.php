<?php

namespace App\Filament\Pages;

use App\Enums\UpdateStatus;
use App\Models\SystemUpdate;
use App\Support\MaintenanceTasks;
use App\Support\Updates\InvalidUpdatePackage;
use App\Support\Updates\UpdateInstaller;
use App\Support\WebpImage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Storage;
use Throwable;
use UnitEnum;

/**
 * Runs whitelisted Artisan tasks and installs update packages from the browser,
 * for hosts without a terminal. Super admins only.
 */
class Maintenance extends Page
{
    protected string $view = 'filament.pages.maintenance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Site maintenance';

    protected static ?string $navigationLabel = 'Maintenance';

    /**
     * @var array{task: string, ok: bool, output: string}|null
     */
    public ?array $lastResult = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public function runTaskAction(): Action
    {
        return Action::make('runTask')
            ->label('Run')
            ->icon(Heroicon::OutlinedPlay)
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments): string => MaintenanceTasks::all()[$arguments['task']]['label'] ?? 'Run task')
            ->modalDescription(fn (array $arguments): string => MaintenanceTasks::all()[$arguments['task']]['description'] ?? '')
            ->modalSubmitActionLabel('Run now')
            ->action(function (array $arguments): void {
                $task = $arguments['task'] ?? null;

                if (! is_string($task) || ! MaintenanceTasks::exists($task)) {
                    return;
                }

                $result = MaintenanceTasks::run($task);
                $this->lastResult = $result + ['task' => $task];

                Notification::make()
                    ->title(MaintenanceTasks::all()[$task]['label'].($result['ok'] ? ' completed' : ' failed'))
                    ->{$result['ok'] ? 'success' : 'danger'}()
                    ->send();
            });
    }

    public function uploadUpdateAction(): Action
    {
        return Action::make('uploadUpdate')
            ->label('Upload update package')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->modalHeading('Upload an update package')
            ->modalDescription('Upload a .zip built with "php artisan softia:make-update". It is checked first; nothing changes until you click Install.')
            ->schema([
                FileUpload::make('package')
                    ->label('Update package (.zip)')
                    ->disk('local')
                    ->directory('updates')
                    ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'])
                    ->maxSize(150 * 1024)
                    ->required(),
            ])
            ->modalSubmitActionLabel('Check package')
            ->action(function (array $data): void {
                $path = Storage::disk('local')->path($data['package']);

                try {
                    $update = app(UpdateInstaller::class)->stage($path, auth()->user());
                } catch (InvalidUpdatePackage $e) {
                    Storage::disk('local')->delete($data['package']);
                    Notification::make()->title('Package rejected')->body($e->getMessage())->danger()->persistent()->send();

                    return;
                }

                Notification::make()
                    ->title("Version {$update->version} is ready to install")
                    ->body(count($update->files).' files will be updated. Review it below, then click Install.')
                    ->success()
                    ->send();
            });
    }

    public function installUpdateAction(): Action
    {
        return Action::make('installUpdate')
            ->label('Install')
            ->icon(Heroicon::OutlinedRocketLaunch)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments): string => 'Install version '.(SystemUpdate::find($arguments['id'] ?? null)?->version ?? ''))
            ->modalDescription('Changed files are backed up first and database migrations run afterwards. Visitors may see errors for a few seconds while files are replaced.')
            ->modalSubmitActionLabel('Install now')
            ->action(function (array $arguments): void {
                $update = SystemUpdate::find($arguments['id'] ?? null);

                if (! $update) {
                    return;
                }

                try {
                    $update = app(UpdateInstaller::class)->apply($update);
                } catch (Throwable $e) {
                    Notification::make()->title('Install failed')->body($e->getMessage())->danger()->persistent()->send();

                    return;
                }

                $update->status === UpdateStatus::Applied
                    ? Notification::make()->title("Version {$update->version} installed")->success()->send()
                    : Notification::make()->title('Install failed and was reverted')->body('See the log below.')->danger()->persistent()->send();
            });
    }

    public function discardUpdateAction(): Action
    {
        return Action::make('discardUpdate')
            ->label('Discard')
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (array $arguments): void {
                if ($update = SystemUpdate::find($arguments['id'] ?? null)) {
                    app(UpdateInstaller::class)->discard($update);
                }

                Notification::make()->title('Package discarded')->send();
            });
    }

    public function rollbackUpdateAction(): Action
    {
        return Action::make('rollbackUpdate')
            ->label('Roll back')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription('Restores the files this update replaced and removes files it added. Database changes are not reversed.')
            ->action(function (array $arguments): void {
                $update = SystemUpdate::find($arguments['id'] ?? null);

                try {
                    app(UpdateInstaller::class)->rollback($update);
                    Notification::make()->title("Version {$update->version} rolled back")->success()->send();
                } catch (Throwable $e) {
                    Notification::make()->title('Roll back failed')->body($e->getMessage())->danger()->persistent()->send();
                }
            });
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'tasks' => MaintenanceTasks::all(),
            'pendingMigrations' => $this->pendingMigrationCount(),
            'webpSupported' => WebpImage::isSupported(),
            'zipSupported' => class_exists(\ZipArchive::class),
            'uploadLimit' => $this->uploadLimit(),
            'pendingUpdate' => SystemUpdate::query()->where('status', UpdateStatus::Pending)->latest()->first(),
            'updates' => SystemUpdate::query()->where('status', '!=', UpdateStatus::Pending)->latest()->take(10)->get(),
        ];
    }

    private function uploadLimit(): string
    {
        return collect(['upload_max_filesize', 'post_max_size'])
            ->map(fn (string $key): string => "{$key} = ".ini_get($key))
            ->implode(' · ');
    }

    private function pendingMigrationCount(): ?int
    {
        try {
            /** @var Migrator $migrator */
            $migrator = app('migrator');

            if (! $migrator->repositoryExists()) {
                return null;
            }

            $files = $migrator->getMigrationFiles([database_path('migrations')]);

            return count(array_diff(array_keys($files), $migrator->getRepository()->getRan()));
        } catch (Throwable) {
            return null;
        }
    }
}
