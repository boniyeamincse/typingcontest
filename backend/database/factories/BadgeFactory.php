<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name'              => ucwords($name),
            'slug'              => Str::slug($name),
            'icon_url'          => null,
            'description'       => $this->faker->sentence(),
            'requirement_type'  => $this->faker->randomElement(['contest_count', 'accuracy', 'wpm', 'rank', 'special']),
            'requirement_value' => $this->faker->numberBetween(1, 100),
            'is_premium'        => false,
        ];
    }
}
