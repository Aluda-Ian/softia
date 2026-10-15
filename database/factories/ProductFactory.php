<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));
        $retailPrice = fake()->numberBetween(100, 5000);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => Str::upper(fake()->unique()->bothify('SFT-####??')),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'unit' => 'Pack of 10',
            'retail_price' => $retailPrice,
            'wholesale_price' => round($retailPrice * 0.85),
            'min_wholesale_qty' => 10,
            'stock' => 100,
            'low_stock_threshold' => 10,
            'is_featured' => false,
            'is_active' => true,
        ];
    }

    public function quoteOnly(): static
    {
        return $this->state(['retail_price' => null]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }
}
