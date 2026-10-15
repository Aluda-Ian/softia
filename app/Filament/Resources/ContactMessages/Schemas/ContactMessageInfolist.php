<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Models\ContactMessage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make(fn (ContactMessage $record): string => $record->topicLabel())
                    ->schema([
                        TextEntry::make('message')
                            ->hiddenLabel()
                            ->prose(),
                    ])
                    ->columnSpan(['lg' => 2]),
                Section::make('From')
                    ->schema([
                        TextEntry::make('name')->weight('bold'),
                        TextEntry::make('email')
                            ->url(fn (ContactMessage $record): string => "mailto:{$record->email}")
                            ->copyable(),
                        TextEntry::make('phone')
                            ->placeholder('—')
                            ->url(fn (ContactMessage $record): ?string => $record->phone ? 'tel:'.preg_replace('/\s+/', '', $record->phone) : null),
                        TextEntry::make('company')->placeholder('—'),
                        TextEntry::make('created_at')->label('Received')->dateTime('j M Y, H:i'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ]);
    }
}
