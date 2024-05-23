<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'=> fake()->unique()->numberBetween(1, 12),
            'phone' => fake()->phoneNumber(),
            'address'=> fake()->address(),
            'birthday'=> fake()->date,
            'gender'=> fake()->numberBetween(1,2),
            'avatar'=>'https://randomuser.me/api/portraits/men/' . fake()->numberBetween(1, 100) . '.jpg',
            'position_id'=> fake()->numberBetween(1, 10),
            'division_id' => fake()->numberBetween(3, 12),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'deleted_at' => null
        ];
    }
}
