<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '07'.fake()->numerify('########'),
            'company' => null,
            'topic' => 'other',
            'message' => fake()->paragraph(),
        ];
    }

    public function read(): static
    {
        return $this->state(['read_at' => now()]);
    }
}
