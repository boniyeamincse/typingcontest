<?php

namespace Database\Factories;

use App\Models\Contest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contest>
 */
class ContestFactory extends Factory
{
    public function definition(): array
    {
        $starts = now()->addMinutes(fake()->numberBetween(5, 120));

        return [
            'title'            => fake()->sentence(4),
            'type'             => fake()->randomElement(['daily', 'weekly', 'monthly']),
            'status'           => 'published',
            'text_content'     => fake()->paragraph(3),
            'duration_seconds' => fake()->randomElement([30, 60, 90, 120]),
            'starts_at'        => $starts,
            'ends_at'          => $starts->clone()->addHours(2),
            'created_by'       => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status'    => 'active',
            'starts_at' => now()->subMinutes(5),
            'ends_at'   => now()->addMinutes(55),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status'  => 'completed',
            'ends_at' => now()->subHour(),
        ]);
    }
}
