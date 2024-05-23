<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profile;
class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Profile::factory()->count(12)->create();
        // Profile::factory()->count(1)->create(['user_id' => 1]);
        Profile::factory()->count(1)->create(['user_id' => 2]);
    }
}
