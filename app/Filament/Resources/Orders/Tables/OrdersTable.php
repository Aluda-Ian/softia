<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->weight('bold')
                    ->copyable()
                    ->description(fn (Order $record): string => $record->created_at->format('d M Y, H:i')),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(['customer_name', 'company_name', 'email', 'phone'])
                    ->description(fn (Order $record): ?string => $record->company_name ?? $record->phone),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->description(fn (Order $record): string => $record->payment_method->getLabel()),
                TextColumn::make('total')
                    ->money('KES')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(OrderType::class),
                SelectFilter::make('status')
                    ->options(OrderStatus::class)
                    ->multiple(),
                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options(PaymentStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    Action::make('updateStatus')
                        ->label('Update status')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->fillForm(fn (Order $record): array => ['status' => $record->status])
                        ->schema([
                            Select::make('status')
                                ->options(OrderStatus::class)
                                ->required(),
                        ])
                        ->action(function (Order $record, array $data): void {
                            $record->update(['status' => $data['status']]);

                            Notification::make()->title("{$record->reference} updated")->success()->send();
                        }),
                    Action::make('markPaid')
                        ->label('Mark as paid')
                        ->icon(Heroicon::OutlinedBanknotes)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription(fn (Order $record): string => $record->payment_reference
                            ? "Confirm you have verified payment {$record->payment_reference}."
                            : 'Confirm payment has been received.')
                        ->visible(fn (Order $record): bool => $record->payment_status !== PaymentStatus::Paid)
                        ->action(function (Order $record): void {
                            $record->update(['payment_status' => PaymentStatus::Paid]);

                            Notification::make()->title("{$record->reference} marked as paid")->success()->send();
                        }),
                ]),
            ]);
    }
}
