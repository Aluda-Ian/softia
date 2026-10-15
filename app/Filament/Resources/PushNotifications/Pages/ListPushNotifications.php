<?php

namespace App\Filament\Resources\PushNotifications\Pages;

use App\Filament\Resources\PushNotifications\PushNotificationResource;
use App\Models\PushSubscription;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPushNotifications extends ListRecords
{
    protected static string $resource = PushNotificationResource::class;

    public function getSubheading(): ?string
    {
        $devices = PushSubscription::query()->count();

        return "{$devices} ".str('device')->plural($devices).' subscribed. Visitors subscribe with the bell icon on the website.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New notification')
                ->icon('heroicon-o-bell-alert'),
        ];
    }
}
