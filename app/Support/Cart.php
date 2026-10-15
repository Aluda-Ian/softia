<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Session cart storing [product_id => quantity].
 */
class Cart
{
    private const SESSION_KEY = 'cart';

    public function add(Product $product, int $quantity): void
    {
        $items = $this->raw();
        $items[$product->id] = min(9999, ($items[$product->id] ?? 0) + max(1, $quantity));

        session()->put(self::SESSION_KEY, $items);
    }

    public function update(Product $product, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($product);

            return;
        }

        $items = $this->raw();
        $items[$product->id] = min(9999, $quantity);

        session()->put(self::SESSION_KEY, $items);
    }

    public function remove(Product $product): void
    {
        $items = $this->raw();
        unset($items[$product->id]);

        session()->put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    /**
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        $items = $this->raw();

        if ($items === []) {
            return collect();
        }

        return Product::query()
            ->active()
            ->whereIn('id', array_keys($items))
            ->with('category.parent')
            ->get()
            ->map(fn (Product $product): CartLine => new CartLine($product, $items[$product->id]))
            ->values();
    }

    /**
     * Total of priced lines. Quote-only lines are priced later by Softia.
     */
    public function subtotal(): float
    {
        return $this->lines()->sum(fn (CartLine $line): float => $line->lineTotal() ?? 0);
    }

    public function hasQuoteOnlyItems(): bool
    {
        return $this->lines()->contains(fn (CartLine $line): bool => $line->product->isQuoteOnly());
    }

    /**
     * @return array<int, int>
     */
    private function raw(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }
}
