<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@videoportal.com'],
            [
                'name'     => 'Admin',
                'email'    => 'admin@videoportal.com',
                'password' => Hash::make('password'),
            ]
        );
    }
}
