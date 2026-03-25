<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Accountant;

class AccountantSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Create USER for accountant
        $user = User::updateOrCreate(
            ['email' => 'accountant@school.com'],
            [
                'name'      => 'Accountant One',
                'password'  => Hash::make('password'),
                'role'      => 'accountant',
                'school_id' => 1,
                'is_active' => 1,
            ]
        );

        // 2️ Create ACCOUNTANT profile
        Accountant::updateOrCreate(
            ['user_id' => $user->id],
            [
                'school_id' => 1,
                'name'      => 'Accountant One',
                'email'     => 'accountant@school.com',
                'phone'     => '8888888888',
                'is_active' => 1,
            ]
        );
    }
}
