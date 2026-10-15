<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('read_at')
                    ->label('')
                    ->state(fn (ContactMessage $record): bool => $record->read_at === null)
                    ->icon(fn (bool $state): ?Heroicon => $state ? Heroicon::Envelope : Heroicon::OutlinedEnvelopeOpen)
                    ->color(fn (bool $state): string => $state ? 'primary' : 'gray')
                    ->tooltip(fn (bool $state): string => $state ? 'Unread' : 'Read'),
                TextColumn::make('name')
                    ->searchable(['name', 'email', 'company', 'message'])
                    ->weight(fn (ContactMessage $record): string => $record->read_at ? 'normal' : 'bold')
                    ->description(fn (ContactMessage $record): string => $record->company ?? $record->email),
                TextColumn::make('topic')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ContactMessage::TOPICS[$state] ?? $state),
                TextColumn::make('message')
                    ->limit(70)
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('unread')
                    ->placeholder('All')
                    ->trueLabel('Unread')
                    ->falseLabel('Read')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('read_at'),
                        false: fn (Builder $query) => $query->whereNotNull('read_at'),
                    ),
                SelectFilter::make('topic')
                    ->options(ContactMessage::TOPICS),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markRead')
                        ->label('Mark as read')
                        ->icon(Heroicon::OutlinedEnvelopeOpen)
                        ->action(fn (Collection $records) => $records->each->update(['read_at' => now()]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
