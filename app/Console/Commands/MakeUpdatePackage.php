<?php

namespace App\Console\Commands;

use App\Support\Updates\UpdateInstaller;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use ZipArchive;

/**
 * Builds a release .zip to upload in Admin > System > Maintenance.
 */
#[Signature('softia:make-update
    {version : Release version, e.g. 1.2.0}
    {--notes= : What changed (shown in the admin)}
    {--since= : Only include files changed after this date/time, e.g. "2026-10-15 09:00"}
    {--with-vendor : Include vendor/ (needed after composer require/update)}
    {--no-migrations : Do not run database migrations after installing}')]
#[Description('Package the site into an update .zip for upload in the admin')]
class MakeUpdatePackage extends Command
{
    /**
     * Folders and files that make up the deployable app.
     */
    private const INCLUDE = ['app', 'bootstrap', 'config', 'database', 'lang', 'public', 'resources', 'routes', 'artisan', 'composer.json', 'composer.lock'];

    public function handle(): int
    {
        $since = $this->option('since') ? CarbonImmutable::parse($this->option('since')) : null;
        $include = self::INCLUDE;

        if ($this->option('with-vendor')) {
            $include[] = 'vendor';
        }

        $files = collect($include)
            ->filter(fn (string $path): bool => file_exists(base_path($path)))
            ->flatMap(fn (string $path): array => is_dir(base_path($path))
                ? array_map(fn (SplFileInfo $file): string => $path.'/'.str_replace('\\', '/', $file->getRelativePathname()), File::allFiles(base_path($path), true))
                : [$path])
            ->reject(fn (string $path): bool => $this->isExcluded($path))
            ->when($since, fn ($files) => $files->filter(fn (string $path): bool => filemtime(base_path($path)) >= $since->getTimestamp()))
            ->values();

        if ($files->isEmpty()) {
            $this->warn('No files to package.');

            return self::FAILURE;
        }

        $version = (string) $this->argument('version');
        $target = storage_path('app/private/releases/softia-update-'.preg_replace('/[^A-Za-z0-9._-]/', '-', $version).'.zip');
        File::ensureDirectoryExists(dirname($target));
        File::delete($target);

        $zip = new ZipArchive;
        $zip->open($target, ZipArchive::CREATE);

        foreach ($files as $file) {
            $zip->addFile(base_path($file), $file);
        }

        $zip->addFromString(UpdateInstaller::MANIFEST, json_encode([
            'version' => $version,
            'notes' => $this->option('notes'),
            'created_at' => now()->toIso8601String(),
            'migrations' => ! $this->option('no-migrations'),
            'filament_assets' => (bool) $this->option('with-vendor'),
        ], JSON_PRETTY_PRINT));

        $zip->close();

        $this->components->info("Packaged {$files->count()} files into {$target}");
        $this->components->bulletList([
            'Size: '.number_format(filesize($target) / 1024 / 1024, 1).' MB',
            'Upload it in Admin > System > Maintenance > Install an update.',
        ]);

        return self::SUCCESS;
    }

    private function isExcluded(string $path): bool
    {
        foreach (UpdateInstaller::PROTECTED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return str_starts_with($path, 'public/hot')
            || str_starts_with($path, 'database/database.sqlite')
            || str_ends_with($path, '.DS_Store');
    }
}
