<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasPermission('orders');
    }

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest orders & quotes';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('reference')
                    ->weight('bold')
                    ->description(fn (Order $record): string => $record->created_at->diffForHumans()),
                TextColumn::make('type')->badge(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (Order $record): ?string => $record->company_name),
                TextColumn::make('status')->badge(),
                TextColumn::make('payment_status')->label('Payment')->badge(),
                TextColumn::make('total')->money('KES'),
            ])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('all')
                    ->label('View all')
                    ->link()
                    ->url(OrderResource::getUrl('index')),
            ]);
    }
}
