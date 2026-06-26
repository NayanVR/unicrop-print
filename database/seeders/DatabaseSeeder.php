<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Setting;
use App\Models\Size;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@unicrop.test',
            'password' => bcrypt('password'),
            'role' => Role::Admin,
        ]);

        $a4 = Size::create(['name' => 'A4 Standard (8.27 x 11.69)', 'rate' => 10, 'is_default' => true]);
        Size::create(['name' => 'A3 Premium (11.69 x 16.53)', 'rate' => 20]);
        Size::create(['name' => '12 x 18 inch (Digital)', 'rate' => 30]);

        Setting::set('cutting_rate', '5');
    }
}
