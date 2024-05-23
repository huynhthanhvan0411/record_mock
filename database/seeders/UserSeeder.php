<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 2 bản ghi với role_id = 1
        User::factory()->count(2)->create(['role_id' => 1]);

        // Tạo 10 bản ghi với role_id = 2
        User::factory()->count(10)->create(['role_id' => 2]);
    }
}
