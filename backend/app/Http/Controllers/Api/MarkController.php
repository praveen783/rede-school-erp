<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TeacherPermissionService;
use App\Enums\Role;


class MarkController extends Controller
{
    public function store(Request $request)
    {
        // 1️ Base validation
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required|array|min:1',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.marks_obtained' => 'nullable|numeric|min:0',
            'marks.*.is_absent' => 'nullable|boolean',
        ]);

        // 2️ Authenticated user
        $user = auth()->user();

        // 3️ SUPER_ADMIN extra validation
        if ($user->role === Role::SUPER_ADMIN) {
            $request->validate([
                'school_id' => 'required|exists:schools,id',
                'academic_year_id' => 'required|exists:academic_years,id',
            ]);
        }

        // 4️ Resolve school context (YOUR CODE GOES HERE)
        if ($user->school_id && $user->academic_year_id) {
            // School Admin / Teacher
            $schoolId = $user->school_id;
            $academicYearId = $user->academic_year_id;
        } else {
            // SUPER_ADMIN
            $schoolId = $request->school_id ?? null;
            $academicYearId = $request->academic_year_id ?? null;
        }

        // 5️ Final safety check
        if (!$schoolId || !$academicYearId) {
            return response()->json([
                'message' => 'School context is required for marks entry'
            ], 422);
        }
        // 5.1 TEACHER permission check (ADD HERE)
        if ($user->role === Role::TEACHER) {

            $allowed = TeacherPermissionService::isAssigned(
                $user->id,
                $academicYearId,
                $request->class_id,
                $request->section_id,
                $request->subject_id
            );

            if (! $allowed) {
                return response()->json([
                    'message' => 'You are not assigned to this class and subject'
                ], 403);
            }
        }
        // 6️ Exam–Subject validation (already implemented)
        $exam = Exam::with('subjects')->findOrFail($request->exam_id);

        $subject = $exam->subjects
            ->where('id', $request->subject_id)
            ->first();

        if (!$subject) {
            return response()->json([
                'message' => 'Subject is not mapped to this exam'
            ], 422);
        }

        $maxMarks = $subject->pivot->max_marks;

        $exam = Exam::findOrFail($request->exam_id);

        if ($exam->is_result_published) {
            return response()->json([
                'message' => 'Results are published. Marks cannot be modified.'
            ], 403);
        }
        // 7️ Save marks safely
        DB::transaction(function () use (
            $request,
            $schoolId,
            $academicYearId,
            $maxMarks
        ) {
            foreach ($request->marks as $entry) {

                if (!empty($entry['is_absent']) && isset($entry['marks_obtained'])) {
                    throw new \Exception('Absent student cannot have marks');
                }

                if (isset($entry['marks_obtained']) && $entry['marks_obtained'] > $maxMarks) {
                    throw new \Exception('Marks cannot exceed max marks');
                }

                Mark::updateOrCreate(
                    [
                        'exam_id' => $request->exam_id,
                        'subject_id' => $request->subject_id,
                        'student_id' => $entry['student_id'],
                    ],
                    [
                        'school_id' => $schoolId,
                        'academic_year_id' => $academicYearId,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'marks_obtained' => $entry['marks_obtained'] ?? null,
                        'is_absent' => $entry['is_absent'] ?? false,
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Marks validated and saved successfully'
        ], 200);
    }

}
