<?php

namespace Database\Factories;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoMeta>
 */
class SeoMetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meta_title' => fake()->sentence(5),
            'meta_description' => fake()->sentence(18),
            'robots' => 'index, follow',
        ];
    }

    public function forRoute(string $routeName): static
    {
        return $this->state(['route_name' => $routeName]);
    }
}
