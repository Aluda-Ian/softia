<?php

namespace App\Filament\Resources\PushNotifications;

use App\Filament\Concerns\RequiresTeamPermission;
use App\Filament\Resources\PushNotifications\Pages\CreatePushNotification;
use App\Filament\Resources\PushNotifications\Pages\ListPushNotifications;
use App\Filament\Resources\PushNotifications\Schemas\PushNotificationForm;
use App\Filament\Resources\PushNotifications\Tables\PushNotificationsTable;
use App\Models\PushNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PushNotificationResource extends Resource
{
    use RequiresTeamPermission;

    public static function teamPermission(): string
    {
        return 'notifications';
    }

    protected static ?string $model = PushNotification::class;

    protected static ?string $slug = 'push-notifications';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Push notifications';

    protected static ?string $modelLabel = 'push notification';

    /**
     * Sent notifications are a record of what went out; they can't be edited.
     */
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return PushNotificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PushNotificationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPushNotifications::route('/'),
            'create' => CreatePushNotification::route('/create'),
        ];
    }
}
