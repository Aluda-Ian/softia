<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Models\Team;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Team')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('description')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Permissions')
                    ->description('Sections members of this team can open. Users, settings and updates are for super admins only.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->hiddenLabel()
                            ->options(Team::PERMISSIONS)
                            ->columns(2)
                            ->bulkToggleable(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
