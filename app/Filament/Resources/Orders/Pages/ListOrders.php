<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'quotes' => Tab::make('Quotation requests')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', OrderType::Quote)),
            'orders' => Tab::make('Retail orders')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', OrderType::Order)),
            'open' => Tab::make('Open')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('status', [OrderStatus::Completed, OrderStatus::Cancelled])),
        ];
    }
}
