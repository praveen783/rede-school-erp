<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\SchoolClass;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all schools
        $schools = School::all();

        foreach ($schools as $school) {

            // Define classes to create per school
            $classes = [
                'Nursery',
                'LKG',
                'UKG',
                'Class 1',
                'Class 2',
                'Class 3',
                'Class 4',
                'Class 5',
                'Class 6',
                'Class 7',
                'Class 8',
                'Class 9',
                'Class 10',
                'Class 11',
                'Class 12',
            ];

            foreach ($classes as $className) {
                SchoolClass::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'name'      => $className,
                    ],
                    [
                        // no extra fields for now
                    ]
                );
            }
        }
    }
}
