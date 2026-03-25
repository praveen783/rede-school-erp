<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClassWiseExamSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $schoolId       = 1;
            $academicYearId = 1;

            // 6 Exams per section
            $examTemplates = [
                ['name' => 'Unit Test 1',      'offset' => 10,  'duration' => 2],
                ['name' => 'Unit Test 2',      'offset' => 30,  'duration' => 2],
                ['name' => 'Quarterly Exam',   'offset' => 60,  'duration' => 5],
                ['name' => 'Half Yearly Exam', 'offset' => 100, 'duration' => 7],
                ['name' => 'Pre Final Exam',   'offset' => 140, 'duration' => 5],
                ['name' => 'Final Exam',       'offset' => 170, 'duration' => 10],
            ];

            $classes = SchoolClass::where('school_id', $schoolId)->get();

            foreach ($classes as $class) {

                $sections = Section::where('class_id', $class->id)->get();

                foreach ($sections as $section) {

                    foreach ($examTemplates as $template) {

                        $startDate = Carbon::now()
                            ->startOfYear()
                            ->addDays($template['offset']);

                        $endDate = (clone $startDate)
                            ->addDays($template['duration']);

                        Exam::create([
                            'school_id'           => $schoolId,
                            'academic_year_id'    => $academicYearId,
                            'class_id'            => $class->id,
                            'section_id'          => $section->id,
                            'name'                => $template['name'],
                            'start_date'          => $startDate,
                            'end_date'            => $endDate,
                            'status'              => 'draft',
                            'is_result_published' => 0,
                            'is_active'           => 1,
                        ]);
                    }
                }
            }
        });
    }
}
