<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vibe;
use Illuminate\Database\Eloquent\Factories\Factory;

class VibeFactory extends Factory
{
    protected $model = Vibe::class;

    public function definition(): array
    {
        return ['created_by' => User::factory(), 'publisher_type' => 'user', 'publisher_id' => fn (array $attributes) => $attributes['created_by'], 'caption' => fake()->sentence(), 'allow_coin_gifts' => true, 'visibility' => 'public', 'status' => 'published', 'published_at' => now()];
    }
}
