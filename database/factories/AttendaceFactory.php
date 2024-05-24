<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AttendaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake(\App\Models\User::class)->create()->id,
            'date' => $this->faker->dateTimeThisYear(),
            'check_in' => $this->faker->dateTimeThisYear(),
            'check_out' => $this->faker->dateTimeThisYear(),
            'total_hours' => $this->faker->randomDigit,
            'late_minutes' => $this->faker->randomDigit,
        ];
    }
}
