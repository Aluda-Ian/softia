<?php

namespace App\Support;

use App\Models\Product;

class CartLine
{
    public function __construct(
        public readonly Product $product,
        public readonly int $quantity,
    ) {}

    /**
     * Wholesale price applies once the line reaches the minimum wholesale quantity.
     */
    public function unitPrice(): ?float
    {
        $product = $this->product;

        if ($product->wholesale_price !== null && $this->quantity >= $product->min_wholesale_qty) {
            return (float) $product->wholesale_price;
        }

        return $product->retail_price !== null ? (float) $product->retail_price : null;
    }

    public function isWholesale(): bool
    {
        return $this->product->wholesale_price !== null
            && $this->quantity >= $this->product->min_wholesale_qty;
    }

    public function lineTotal(): ?float
    {
        $unitPrice = $this->unitPrice();

        return $unitPrice === null ? null : $unitPrice * $this->quantity;
    }
}
