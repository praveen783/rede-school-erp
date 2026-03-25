<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
  
class TeachersSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            [
                'employee_code'   => 'EMP101',
                'name'            => 'Anil Kumar',
                'email'           => 'anil@school.com',
                'phone'           => '9876543210',
                'gender'          => 'male',
                'date_of_joining' => '2022-06-15',
                'is_active'       => 1,
            ],
            [
                'employee_code'   => 'EMP102',
                'name'            => 'Sunita Sharma',
                'email'           => 'sunita@school.com',
                'phone'           => '9876543211',
                'gender'          => 'female',
                'date_of_joining' => '2021-07-10',
                'is_active'       => 1,
            ],
            [
                'employee_code'   => 'EMP103',
                'name'            => 'Ravi Teja',
                'email'           => 'ravi@school.com',
                'phone'           => null,
                'gender'          => 'male',
                'date_of_joining' => '2020-01-05',
                'is_active'       => 0, // inactive teacher (important for UI testing)
            ],
            [
                'employee_code'   => 'EMP104',
                'name'            => 'Neha Verma',
                'email'           => 'neha@school.com',
                'phone'           => '9876543212',
                'gender'          => 'female',
                'date_of_joining' => '2023-04-01',
                'is_active'       => 1,
            ],
        ];

        foreach ($teachers as $teacher) {
            Teacher::updateOrCreate(
                [
                    'school_id' => 1,
                    'employee_code' => $teacher['employee_code'],
                ],
                [
                    'name'            => $teacher['name'],
                    'email'           => $teacher['email'],
                    'phone'           => $teacher['phone'],
                    'gender'          => $teacher['gender'],
                    'date_of_joining' => $teacher['date_of_joining'],
                    'is_active'       => $teacher['is_active'],
                ]
            );
        }
    }
}
