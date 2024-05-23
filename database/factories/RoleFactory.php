<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            ['name' => 'Admin', 'status' =>1,'create_at'=>now(),'update_at'=>now(),'deleted_at'=>null,],
            ['name' => 'User','status' =>1,'create_at'=>now(),'update_at'=>now(),'deleted_at'=>null,],
        ];
    }
}
