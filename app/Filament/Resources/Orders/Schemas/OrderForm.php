<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Staff edit status, payment and (for quotes) unit prices. Customer details
 * are read-only on the view page; totals are recalculated after saving.
 */
class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Items')
                    ->description('Set unit prices to turn a quotation request into a priced quote.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('product_name')
                                    ->disabled()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('KES'),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->reorderable(false),
                    ])
                    ->columnSpan(['lg' => 2]),

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->options(OrderStatus::class)
                            ->required()
                            ->selectablePlaceholder(false),
                        Select::make('payment_status')
                            ->options(PaymentStatus::class)
                            ->required()
                            ->selectablePlaceholder(false),
                        TextInput::make('payment_reference')
                            ->label('Payment reference')
                            ->helperText('M-Pesa code or invoice number.')
                            ->maxLength(255),
                        TextInput::make('delivery_fee')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('KES')
                            ->default(0)
                            ->required(),
                        Textarea::make('notes')
                            ->label('Customer notes')
                            ->rows(3),
                    ])
                    ->columnSpan(['lg' => 1]),
            ]);
    }
}
