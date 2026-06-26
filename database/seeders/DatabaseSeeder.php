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
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@unicrop.test')],
            [
                'name' => 'System Admin',
                'password' => bcrypt(env('ADMIN_PASSWORD', 'password')),
                'role' => Role::Admin,
            ]
        );

        if (Size::count() === 0) {
            Size::create(['name' => 'A4 Standard (8.27 x 11.69)', 'rate' => 10, 'is_default' => true]);
            Size::create(['name' => 'A3 Premium (11.69 x 16.53)', 'rate' => 20]);
            Size::create(['name' => '12 x 18 inch (Digital)', 'rate' => 30]);
        }

        Setting::set('cutting_rate', '5');
    }
}
