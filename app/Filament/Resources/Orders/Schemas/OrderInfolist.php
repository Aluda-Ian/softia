<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Items')
                        ->schema([
                            RepeatableEntry::make('items')
                                ->hiddenLabel()
                                ->table([
                                    RepeatableEntry\TableColumn::make('Product'),
                                    RepeatableEntry\TableColumn::make('SKU'),
                                    RepeatableEntry\TableColumn::make('Qty'),
                                    RepeatableEntry\TableColumn::make('Unit price'),
                                    RepeatableEntry\TableColumn::make('Line total'),
                                ])
                                ->schema([
                                    TextEntry::make('product_name'),
                                    TextEntry::make('sku')->color('gray'),
                                    TextEntry::make('quantity'),
                                    TextEntry::make('unit_price')->money('KES')->placeholder('To be quoted'),
                                    TextEntry::make('line_total')->money('KES')->placeholder('—'),
                                ]),
                        ]),
                    Section::make('Customer')
                        ->schema([
                            TextEntry::make('customer_name'),
                            TextEntry::make('customer_type')->badge(),
                            TextEntry::make('company_name')->placeholder('—'),
                            TextEntry::make('kra_pin')->label('KRA PIN')->placeholder('—'),
                            TextEntry::make('email')
                                ->url(fn (Order $record): string => "mailto:{$record->email}")
                                ->copyable(),
                            TextEntry::make('phone')
                                ->url(fn (Order $record): string => 'tel:'.preg_replace('/\s+/', '', $record->phone))
                                ->copyable(),
                            TextEntry::make('delivery_address')->columnSpanFull(),
                            TextEntry::make('notes')->placeholder('No notes')->columnSpanFull(),
                        ])
                        ->columns(2),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Order')
                        ->schema([
                            TextEntry::make('reference')->weight('bold')->copyable(),
                            TextEntry::make('type')->badge(),
                            TextEntry::make('status')->badge(),
                            TextEntry::make('created_at')->label('Placed')->dateTime('d M Y, H:i'),
                        ]),
                    Section::make('Payment')
                        ->schema([
                            TextEntry::make('payment_method'),
                            TextEntry::make('payment_status')->badge(),
                            TextEntry::make('payment_reference')->placeholder('—')->copyable(),
                            TextEntry::make('subtotal')->money('KES'),
                            TextEntry::make('delivery_fee')->money('KES'),
                            TextEntry::make('total')->money('KES')->weight('bold')->size('lg'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
