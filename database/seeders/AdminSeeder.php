<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(

            [
                'email' => 'admin@portfoliohub.com'
            ],

            [

                'name' => 'Platform Admin',

                'phone' => '0000000000',

                'password' => Hash::make('Admin@123'),

                'profession' => 'Administrator',

                'career_goal' => 'Manage Platform',

                'package_name' => 'Platinum',

                'role' => 'admin',

                'is_active' => true,

            ]

        );
    }
}