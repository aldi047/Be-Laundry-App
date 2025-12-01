<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'email' => 'admin@laundry.com',
            'password' => Hash::make('password'),
            'role_id' => 1, // Admin role
        ]);

        User::create([
            'username' => 'aldiazmi',
            'email' => 'aldiazmi@krobok.com',
            'password' => Hash::make('aldiazmi'),
            'role_id' => 2, // User role
        ]);

    }
}
