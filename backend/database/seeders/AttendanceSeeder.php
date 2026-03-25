<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $students = DB::table('students')->get();

        $start = Carbon::create(2025, 6, 1);
        $end = Carbon::create(2026, 3, 9);

        while ($start->lte($end)) {

            // Skip Sunday
            if (!$start->isSunday()) {

                $batch = [];

                foreach ($students as $student) {

                    $status = rand(1, 100) <= 90 ? 'present' : 'absent';

                    $batch[] = [
                        'school_id' => $student->school_id,
                        'academic_year_id' => $student->academic_year_id,
                        'class_id' => $student->class_id,
                        'section_id' => $student->section_id,
                        'student_id' => $student->id,
                        'attendance_date' => $start->toDateString(),
                        'status' => $status,
                        'marked_by' => null,
                        'marked_role' => 'teacher',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                DB::table('attendances')->insert($batch);
            }

            $start->addDay();
        }
    }
}