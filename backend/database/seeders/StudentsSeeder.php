<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Category;
use Faker\Factory as Faker;

class StudentsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_IN');

        $schools = School::all();

        foreach ($schools as $school) {

            $academicYear = AcademicYear::where('school_id', $school->id)
                ->where('is_active', 1)
                ->first();

            if (!$academicYear) {
                $this->command->warn("No active academic year for school ID {$school->id}");
                continue;
            }

            $classes = SchoolClass::where('school_id', $school->id)->get();

            $categories = Category::where('school_id', $school->id)->pluck('id')->toArray();

            foreach ($classes as $class) {

                $sections = Section::where('class_id', $class->id)->get();

                foreach ($sections as $section) {

                    for ($i = 1; $i <= 10; $i++) {

                        $gender = $i % 2 === 0 ? 'female' : 'male';

                        $studentName = $gender === 'male'
                            ? $faker->firstNameMale . ' ' . $faker->lastName
                            : $faker->firstNameFemale . ' ' . $faker->lastName;

                        $admissionNo = 'ADM-' . $school->id .
                            '-' . $class->id .
                            '-' . $section->id .
                            '-' . str_pad($i, 3, '0', STR_PAD_LEFT);

                        Student::updateOrCreate(
                            [
                                'school_id'    => $school->id,
                                'admission_no' => $admissionNo,
                            ],
                            [
                                'academic_year_id' => $academicYear->id,
                                'class_id'         => $class->id,
                                'section_id'       => $section->id,

                                'name'         => $studentName,
                                'parent_name'  => $faker->name,
                                'gender'       => $gender,

                                'category_id'  => $faker->randomElement($categories),

                                'dob'          => $faker->dateTimeBetween('-15 years', '-5 years')->format('Y-m-d'),
                                'date_of_joining' => now()->format('Y-m-d'),
                                'address'      => $faker->address,
                                'is_active'    => 1,
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('Students seeded successfully (10 per section per class).');
    }
}