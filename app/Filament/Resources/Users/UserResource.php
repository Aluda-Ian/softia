<?php

namespace App\Filament\Resources\Users;

use App\Filament\Concerns\RequiresSuperAdmin;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use UnitEnum;

class UserResource extends Resource
{
    use RequiresSuperAdmin;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'staff member';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Attributes the form may set. Anyone without admin access loses their team and super admin role.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function accountAttributes(array $data): array
    {
        $attributes = Arr::only($data, ['name', 'email', 'password', 'team_id', 'is_admin', 'is_super_admin']);

        if (array_key_exists('is_admin', $attributes) && ! $attributes['is_admin']) {
            $attributes['team_id'] = null;
            $attributes['is_super_admin'] = false;
        }

        return $attributes;
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
