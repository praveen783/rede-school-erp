<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestUsersSeeder extends Seeder
{
    
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@school.com',
                'password' => 'password123',
                'role' => 'super_admin',
            ],
            [
                'name' => 'School Admin',
                'email' => 'admin@school.com',
                'password' => 'password123',
                'role' => 'school_admin',
            ],
            [
                'name' => 'Accountant',
                'email' => 'accountant@school.com',
                'password' => 'password123',
                'role' => 'accountant',
            ],
            [
                'name' => 'Teacher',
                'email' => 'teacher@school.com',
                'password' => 'password123',
                'role' => 'teacher',
            ],
            [
                'name' => 'Student',
                'email' => 'student@school.com',
                'password' => 'password123',
                'role' => 'student',
            ],
            [
                'name' => 'Parent',
                'email' => 'parent@school.com',
                'password' => 'password123',
                'role' => 'parent',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']], // avoid duplicates
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                ]
            );
        }

        $this->command->info('Test users for all roles created successfully.');
    }
}
