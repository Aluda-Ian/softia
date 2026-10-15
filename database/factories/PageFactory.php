<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(3, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'footer_group' => null,
            'sort_order' => 0,
            'is_published' => true,
        ];
    }

    public function inFooter(string $group): static
    {
        return $this->state(['footer_group' => $group]);
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false]);
    }
}
