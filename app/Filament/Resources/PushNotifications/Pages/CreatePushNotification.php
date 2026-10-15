<?php

namespace App\Filament\Resources\PushNotifications\Pages;

use App\Filament\Resources\PushNotifications\PushNotificationResource;
use App\Models\PushNotification;
use App\Models\PushSubscription;
use App\Support\Push\PushSender;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePushNotification extends CreateRecord
{
    protected static string $resource = PushNotificationResource::class;

    protected static ?string $title = 'Send a push notification';

    protected static bool $canCreateAnother = false;

    /**
     * "Reuse" on the history list prefills the form from the query string.
     */
    protected function fillForm(): void
    {
        $this->form->fill(array_filter(request()->only(['title', 'body', 'url'])) + ['url' => url('/')]);
    }

    protected function getCreateFormAction(): Action
    {
        $devices = PushSubscription::query()->count();

        return parent::getCreateFormAction()
            ->label("Send to {$devices} ".str('device')->plural($devices))
            ->icon('heroicon-o-paper-airplane')
            ->requiresConfirmation()
            ->modalHeading('Send this notification now?')
            ->modalDescription("It goes out immediately to {$devices} subscribed ".str('device')->plural($devices).' and cannot be recalled.')
            ->modalSubmitActionLabel('Send now');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $notification = PushNotification::create($data + ['user_id' => auth()->id()]);

        return app(PushSender::class)->send($notification);
    }

    protected function getCreatedNotification(): ?Notification
    {
        /** @var PushNotification $record */
        $record = $this->getRecord();

        return $record->sent_count > 0
            ? Notification::make()->success()->title("Delivered to {$record->sent_count} ".str('device')->plural($record->sent_count))
                ->body($record->failed_count ? "{$record->failed_count} failed ({$record->removed_count} expired subscriptions removed)." : null)
            : Notification::make()->warning()->title('Nothing was delivered')->body($record->error);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
