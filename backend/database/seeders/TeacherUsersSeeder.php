<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherUsersSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Teacher::whereNull('user_id')->get();

        foreach ($teachers as $teacher) {

            // Teacher login email format
            // Example: tch-1@school.local
            $email = 'tch-' . $teacher->id . '@school.local';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $teacher->name,
                    'password' => Hash::make('teacher123'),
                    'role' => 'teacher',
                    'school_id' => $teacher->school_id,
                ]
            );

            // Link teacher → user
            $teacher->update([
                'user_id' => $user->id,
            ]);
        }
    }
}
