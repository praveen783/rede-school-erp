<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@school.com'],
            [
                'name'      => 'School Admin',
                'password'  => Hash::make('password'),
                'role'      => 'school_admin',
                'school_id' => 1,
            ]
        );
    }
}
