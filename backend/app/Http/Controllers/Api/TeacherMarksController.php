<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Student;
use App\Models\Mark;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class TeacherMarksController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'exam_id'              => 'required|integer',
            'class_id'             => 'required|integer',
            'section_id'           => 'required|integer',
            'subject_id'           => 'required|integer',
            'records'              => 'required|array|min:1',
            'records.*.student_id' => 'required|integer',
            'records.*.marks'      => 'nullable|numeric|min:0',
            'records.*.is_absent'  => 'required|boolean',
        ]);

        // Resolve teacher
        $teacher = Teacher::where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Active academic year
        $academicYear = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        // 🔒 Validate exam WITH class & section
        $exam = Exam::where([
            'id'               => $data['exam_id'],
            'school_id'        => $user->school_id,
            'academic_year_id' => $academicYear->id,
            'class_id'         => $data['class_id'],
            'section_id'       => $data['section_id'],
            'status'           => 'published',
            'is_active'        => true,
        ])->first();

        if (! $exam) {
            return response()->json(['message' => 'Invalid exam'], 403);
        }

        if ($exam->is_result_published) {
            return response()->json([
                'message' => 'Results already published. Marks locked.'
            ], 403);
        }

        // 🔒 Teacher assignment check
        $assigned = TeacherAssignment::where([
            'teacher_id' => $teacher->id,
            'class_id'   => $data['class_id'],
            'section_id' => $data['section_id'],
            'subject_id' => $data['subject_id'],
            'is_active'  => true,
        ])->exists();

        if (! $assigned) {
            return response()->json([
                'message' => 'Not authorized for this subject'
            ], 403);
        }

        // 🔒 Subject belongs to exam
        $examSubject = ExamSubject::where([
            'exam_id'    => $exam->id,
            'subject_id' => $data['subject_id'],
        ])->firstOrFail();

        foreach ($data['records'] as $record) {

            $student = Student::where([
                'id'         => $record['student_id'],
                'school_id'  => $user->school_id,
                'class_id'   => $data['class_id'],
                'section_id' => $data['section_id'],
                'is_active'  => true,
            ])->first();

            if (! $student) continue;

            $marks = null;

            if (! $record['is_absent']) {
                if ($record['marks'] === null) {
                    return response()->json([
                        'message' => 'Marks required for present students'
                    ], 422);
                }

                if ($record['marks'] > $examSubject->max_marks) {
                    return response()->json([
                        'message' => 'Marks exceed max marks'
                    ], 422);
                }

                $marks = $record['marks'];
            }

            // ✅ UPSERT (safe re-entry)
            Mark::updateOrCreate(
                [
                    'school_id'        => $user->school_id,
                    'academic_year_id' => $academicYear->id,
                    'exam_id'          => $exam->id,
                    'class_id'         => $data['class_id'],
                    'section_id'       => $data['section_id'],
                    'subject_id'       => $data['subject_id'],
                    'student_id'       => $student->id,
                ],
                [
                    'marks_obtained' => $marks,
                    'is_absent'      => $record['is_absent'],
                ]
            );
        }

        return response()->json([
            'message' => 'Marks saved successfully'
        ], 201);
    }
}
