<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectsSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Languages
            ['name' => 'English',                  'code' => 'ENG'],
            ['name' => 'Hindi',                    'code' => 'HIN'],
            ['name' => 'Sanskrit',                 'code' => 'SAN'],

            // Core
            ['name' => 'Mathematics',              'code' => 'MATH'],
            ['name' => 'Environmental Science',    'code' => 'EVS'],
            ['name' => 'Science',                  'code' => 'SCI'],
            ['name' => 'Social Science',           'code' => 'SST'],

            // Class 9 & 10 split subjects
            ['name' => 'Physics',                  'code' => 'PHY'],
            ['name' => 'Chemistry',                'code' => 'CHEM'],
            ['name' => 'Biology',                  'code' => 'BIO'],
            ['name' => 'History',                  'code' => 'HIST'],
            ['name' => 'Geography',                'code' => 'GEO'],
            ['name' => 'Economics',                'code' => 'ECO'],
            ['name' => 'Political Science',        'code' => 'POL'],

            // Common
            ['name' => 'Computer Science',         'code' => 'CS'],
            ['name' => 'General Knowledge',        'code' => 'GK'],
            ['name' => 'Moral Science',            'code' => 'MS'],
            ['name' => 'Drawing & Craft',          'code' => 'ART'],
            ['name' => 'Physical Education',       'code' => 'PE'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                [
                    'school_id' => 1,
                    'name'      => $subject['name'],
                ],
                [
                    'code'      => $subject['code'],
                    'is_active' => 1,
                ]
            );
        }
    }
}
