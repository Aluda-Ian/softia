<?php

namespace App\Models;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id', 'reference', 'type', 'status', 'customer_type', 'customer_name', 'company_name', 'kra_pin',
    'email', 'phone', 'delivery_address', 'notes', 'payment_method', 'payment_reference',
    'payment_status', 'subtotal', 'delivery_fee', 'total',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'status' => OrderStatus::class,
            'customer_type' => CustomerType::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->reference ??= static::generateReference($order->type);
        });
    }

    public static function generateReference(?OrderType $type): string
    {
        $prefix = $type === OrderType::Quote ? 'QT' : 'SO';

        return $prefix.'-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Customer-facing visual order tracking progression.
     *
     * @return array<int, array{key: string, label: string, description: string, done: bool, current: bool}>
     */
    public function trackingSteps(): array
    {
        if ($this->status === OrderStatus::Cancelled) {
            return [
                ['key' => 'placed', 'label' => 'Order Placed', 'description' => 'Order received', 'done' => true, 'current' => false],
                ['key' => 'cancelled', 'label' => 'Cancelled', 'description' => 'This order was cancelled', 'done' => true, 'current' => true],
            ];
        }

        if ($this->isQuote()) {
            $stages = [
                OrderStatus::Pending->value => 1,
                OrderStatus::Quoted->value => 2,
                OrderStatus::Confirmed->value => 3,
                OrderStatus::Processing->value => 4,
                OrderStatus::Dispatched->value => 5,
                OrderStatus::Completed->value => 6,
            ];
            $currentLevel = $stages[$this->status->value] ?? 1;

            return [
                ['key' => 'quote_submitted', 'label' => 'Quote Requested', 'description' => 'Received by our sales team', 'done' => $currentLevel >= 1, 'current' => $currentLevel === 1],
                ['key' => 'quoted', 'label' => 'Pricing Prepared', 'description' => 'Quote ready with pricing', 'done' => $currentLevel >= 2, 'current' => $currentLevel === 2],
                ['key' => 'confirmed', 'label' => 'Confirmed', 'description' => 'Approved & scheduled', 'done' => $currentLevel >= 3, 'current' => $currentLevel === 3],
                ['key' => 'processing', 'label' => 'Preparing Goods', 'description' => 'Packing at logistics hub', 'done' => $currentLevel >= 4, 'current' => $currentLevel === 4],
                ['key' => 'dispatched', 'label' => 'Dispatched', 'description' => 'On vehicle for delivery', 'done' => $currentLevel >= 5, 'current' => $currentLevel === 5],
                ['key' => 'completed', 'label' => 'Delivered', 'description' => 'Delivered and fulfilled', 'done' => $currentLevel >= 6, 'current' => $currentLevel === 6],
            ];
        }

        $stages = [
            OrderStatus::Pending->value => 1,
            OrderStatus::Confirmed->value => 2,
            OrderStatus::Processing->value => 3,
            OrderStatus::Dispatched->value => 4,
            OrderStatus::Completed->value => 5,
        ];
        $currentLevel = $stages[$this->status->value] ?? 1;

        return [
            ['key' => 'placed', 'label' => 'Order Placed', 'description' => 'Order received & recorded', 'done' => $currentLevel >= 1, 'current' => $currentLevel === 1],
            ['key' => 'confirmed', 'label' => 'Confirmed', 'description' => $this->payment_reference ? 'Payment verified' : 'Order verified by team', 'done' => $currentLevel >= 2, 'current' => $currentLevel === 2],
            ['key' => 'processing', 'label' => 'Preparing Order', 'description' => 'Packaging products at warehouse', 'done' => $currentLevel >= 3, 'current' => $currentLevel === 3],
            ['key' => 'dispatched', 'label' => 'Out for Delivery', 'description' => 'Softia driver is en route to you', 'done' => $currentLevel >= 4, 'current' => $currentLevel === 4],
            ['key' => 'completed', 'label' => 'Delivered', 'description' => 'Delivered to your address', 'done' => $currentLevel >= 5, 'current' => $currentLevel === 5],
        ];
    }

    /**
     * Recompute line totals and order totals, e.g. after staff price a quote.
     */
    public function recalculateTotals(): void
    {
        $this->load('items');

        foreach ($this->items as $item) {
            $item->line_total = $item->unit_price === null ? null : (float) $item->unit_price * $item->quantity;
            $item->save();
        }

        $this->subtotal = $this->items->sum(fn (OrderItem $item): float => (float) $item->line_total);
        $this->total = (float) $this->subtotal + (float) $this->delivery_fee;
        $this->saveQuietly();
    }

    public function hasUnpricedItems(): bool
    {
        return $this->items->contains(fn (OrderItem $item): bool => $item->unit_price === null);
    }

    public function isQuote(): bool
    {
        return $this->type === OrderType::Quote;
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }
}
