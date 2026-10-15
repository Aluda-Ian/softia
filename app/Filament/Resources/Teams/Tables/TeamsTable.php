<?php

namespace App\Filament\Resources\Teams\Tables;

use App\Models\Team;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('members'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Team $record): ?string => $record->description),
                TextColumn::make('permissions')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Team::PERMISSIONS[$state] ?? $state)
                    ->placeholder('No sections'),
                TextColumn::make('members_count')
                    ->label('Members')
                    ->badge()
                    ->color('gray'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription('Members keep their accounts but lose this team’s access until moved to another team.'),
            ]);
    }
}
