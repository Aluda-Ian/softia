<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Account')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->rule(Password::min(12))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'At least 12 characters. Share it with the staff member securely.'
                                : 'Leave blank to keep the current password.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),

                Section::make('Access')
                    ->schema([
                        Toggle::make('is_admin')
                            ->label('Can sign in to the admin')
                            ->default(true)
                            ->live()
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
                        Select::make('team_id')
                            ->label('Team')
                            ->relationship('team', 'name')
                            ->placeholder('No team (dashboard only)')
                            ->helperText('The team decides which sections this person can use.')
                            ->preload()
                            ->visible(fn (Get $get): bool => (bool) $get('is_admin')),
                        Toggle::make('is_super_admin')
                            ->label('Super admin')
                            ->helperText('Full access, including users, settings, payment keys and updates.')
                            ->visible(fn (Get $get): bool => (bool) $get('is_admin'))
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
                    ])
                    ->columnSpan(['lg' => 1]),
            ]);
    }
}
