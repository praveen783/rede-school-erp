<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\AcademicYear;

class ExamSeeder extends Seeder
{
    public function run()
    {
        $schoolId = 1;

        $academicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            throw new \Exception('No active academic year');
        }

        $exams = [
            [
                'name' => 'Unit Test 1',
                'class_id' => 1,
                'section_id' => 1,
                'subjects' => [
                    ['id' => 1, 'max' => 25, 'pass' => 10],
                    ['id' => 2, 'max' => 25, 'pass' => 10],
                ]
            ],
            [
                'name' => 'Mid Term',
                'class_id' => 1,
                'section_id' => 1,
                'subjects' => [
                    ['id' => 1, 'max' => 50, 'pass' => 20],
                    ['id' => 2, 'max' => 50, 'pass' => 20],
                ]
            ],
        ];

        foreach ($exams as $examData) {

            $exam = Exam::create([
                'school_id'        => $schoolId,
                'academic_year_id'=> $academicYear->id,
                'class_id'         => $examData['class_id'],
                'section_id'       => $examData['section_id'],
                'name'             => $examData['name'],
                'start_date'       => now(),
                'end_date'         => now()->addDays(5),
                'status'           => 'published',
                'is_active'        => true,
            ]);

            foreach ($examData['subjects'] as $subject) {
                ExamSubject::create([
                    'exam_id'    => $exam->id,
                    'subject_id' => $subject['id'],
                    'max_marks'  => $subject['max'],
                    'pass_marks'=> $subject['pass'],
                ]);
            }
        }
    }
}
