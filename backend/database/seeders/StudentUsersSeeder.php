<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentUsersSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::whereNull('user_id')->get();

        foreach ($students as $student) {

            // admission_no based login email
            $email = strtolower($student->admission_no) . '@school.local';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $student->name,
                    'password' => Hash::make('student123'),
                    'role' => 'student',
                    'school_id' => $student->school_id,
                ]
            );

            // Link student → user
            $student->update([
                'user_id' => $user->id,
            ]);
        }
    }
}
