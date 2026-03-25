<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\SchoolClass; // adjust if your model name is ClassRoom / SchoolClass
use App\Models\Section;
use Carbon\Carbon;
use DB;

class BulkStudentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $schoolId = 1;
            $academicYearId = 1;

            // 🔐 Get last admission number safely
            $lastAdmission = Student::orderBy('id', 'desc')->value('admission_no');

            $lastNumber = 0;
            if ($lastAdmission && preg_match('/(\d+)$/', $lastAdmission, $matches)) {
                $lastNumber = (int) $matches[1];
            }

            $counter = $lastNumber + 1;

            $classes = SchoolClass::where('school_id', $schoolId)->get();

            foreach ($classes as $class) {

                $sections = Section::where('class_id', $class->id)->get();

                foreach ($sections as $section) {

                    for ($i = 1; $i <= 10; $i++) {

                        Student::create([
                            'school_id'          => $schoolId,
                            'academic_year_id'   => $academicYearId,
                            'class_id'           => $class->id,
                            'section_id'         => $section->id,
                            'admission_no'       => 'ADM-1-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                            'name'               => "Student {$counter}",
                            'parent_name'        => "Parent {$counter}",
                            'address'            => 'Auto generated address',
                            'gender'             => $counter % 2 === 0 ? 'female' : 'male',
                            'dob'                => Carbon::now()->subYears(10)->subDays(rand(1, 365)),
                            'date_of_joining'    => Carbon::now()->subYears(rand(1, 5)),
                            'is_active'          => 1,
                        ]);

                        $counter++;
                    }
                }
            }
        });
    }
}
