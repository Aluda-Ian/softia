<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('team'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (User $record): string => $record->email),
                TextColumn::make('role')
                    ->badge()
                    ->state(fn (User $record): string => match (true) {
                        $record->isSuperAdmin() => 'Super admin',
                        $record->is_admin => 'Staff',
                        default => 'No admin access',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Super admin' => 'primary',
                        'Staff' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->date('j M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('team')
                    ->relationship('team', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->is(auth()->user())),
            ]);
    }
}
