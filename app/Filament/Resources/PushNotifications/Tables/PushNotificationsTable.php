<?php

namespace App\Filament\Resources\PushNotifications\Tables;

use App\Filament\Resources\PushNotifications\PushNotificationResource;
use App\Models\PushNotification;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PushNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->weight('bold')
                    ->searchable(['title', 'body'])
                    ->description(fn (PushNotification $record): string => str($record->body)->limit(80)->toString())
                    ->wrap(),
                TextColumn::make('sent_count')
                    ->label('Delivered')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),
                TextColumn::make('failed_count')
                    ->label('Failed')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray')
                    ->tooltip(fn (PushNotification $record): ?string => $record->error),
                TextColumn::make('user.name')
                    ->label('Sent by')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime('j M Y, H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('sendAgain')
                    ->label('Reuse')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->url(fn (PushNotification $record): string => PushNotificationResource::getUrl('create', [
                        'title' => $record->title,
                        'body' => $record->body,
                        'url' => $record->url,
                    ])),
            ]);
    }
}
