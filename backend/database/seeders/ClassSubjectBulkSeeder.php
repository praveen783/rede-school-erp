<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSubject;
use Illuminate\Support\Facades\DB;

class ClassSubjectBulkSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $schoolId = 1;

            $classes  = SchoolClass::where('school_id', $schoolId)->orderBy('id')->get();
            $subjects = Subject::where('school_id', $schoolId)
                                ->where('is_active', 1)
                                ->get()
                                ->keyBy('name');

            foreach ($classes as $class) {

                // Define subject set per class (Indian School Standard)
                $subjectNames = match ($class->name) {

                    // Nursery to KG
                    'Nursery', 'LKG', 'UKG' => [
                        'English', 'Hindi', 'Mathematics',
                        'Environmental Science', 'Drawing & Craft',
                        'Moral Science',
                    ],

                    // Class 1 & 2
                    'Class 1', 'Class 2' => [
                        'English', 'Hindi', 'Mathematics',
                        'Environmental Science', 'Drawing & Craft',
                        'General Knowledge', 'Moral Science',
                    ],

                    // Class 3, 4 & 5
                    'Class 3', 'Class 4', 'Class 5' => [
                        'English', 'Hindi', 'Mathematics',
                        'Environmental Science', 'General Knowledge',
                        'Computer Science', 'Moral Science',
                    ],

                    // Class 6, 7 & 8
                    'Class 6', 'Class 7', 'Class 8' => [
                        'English', 'Hindi', 'Mathematics',
                        'Science', 'Social Science', 'Sanskrit',
                        'Computer Science', 'General Knowledge',
                    ],

                    // Class 9 & 10
                    'Class 9', 'Class 10' => [
                        'English', 'Hindi', 'Mathematics',
                        'Physics', 'Chemistry', 'Biology',
                        'History', 'Geography', 'Economics',
                        'Political Science', 'Sanskrit',
                        'Computer Science', 'Physical Education',
                    ],

                    default => ['English', 'Mathematics'],
                };

                foreach ($subjectNames as $name) {
                    if (!isset($subjects[$name])) {
                        continue; // skip missing subjects safely
                    }

                    ClassSubject::create([
                        'class_id'   => $class->id,
                        'subject_id' => $subjects[$name]->id,
                    ]);
                }
            }
        });
    }
}
