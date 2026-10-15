<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Inventory\InventoryResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Support\Money;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShopOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->hasPermission('orders') || $user?->hasPermission('inventory'));
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $pendingQuotes = Order::query()->where('type', OrderType::Quote)->where('status', OrderStatus::Pending)->count();
        $openOrders = Order::query()->where('type', OrderType::Order)->whereNotIn('status', [OrderStatus::Completed, OrderStatus::Cancelled])->count();
        $paymentsToVerify = Order::query()->where('payment_status', PaymentStatus::AwaitingVerification)->count();
        $lowStock = Product::query()->active()->whereColumn('stock', '<=', 'low_stock_threshold')->count();
        $paidThisMonth = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('total');

        return [
            Stat::make('Quotes to price', $pendingQuotes)
                ->description('Pending quotation requests')
                ->descriptionIcon(Heroicon::OutlinedDocumentText)
                ->color($pendingQuotes > 0 ? 'warning' : 'success')
                ->url(OrderResource::getUrl('index', ['activeTab' => 'quotes'])),
            Stat::make('Open retail orders', $openOrders)
                ->description($paymentsToVerify > 0 ? "{$paymentsToVerify} M-Pesa payments to verify" : 'All payments verified')
                ->descriptionIcon(Heroicon::OutlinedShoppingBag)
                ->color($paymentsToVerify > 0 ? 'warning' : 'primary')
                ->url(OrderResource::getUrl('index', ['activeTab' => 'orders'])),
            Stat::make('Low stock', $lowStock)
                ->description('Products at or below alert level')
                ->descriptionIcon(Heroicon::OutlinedArchiveBox)
                ->color($lowStock > 0 ? 'danger' : 'success')
                ->url(InventoryResource::getUrl('index')),
            Stat::make('Paid this month', Money::format($paidThisMonth))
                ->description(now()->format('F Y'))
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),
        ];
    }
}
