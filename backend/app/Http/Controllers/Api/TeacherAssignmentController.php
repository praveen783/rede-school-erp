<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\AcademicYear;


class TeacherAssignmentController extends Controller
    {
        /**
         * Assign teacher to class + section + subject
         */
        public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $schoolId = $user->school_id;
        $academicYearId = $user->academic_year_id;

        // Validate section belongs to class
        $section = \App\Models\Section::where('id', $data['section_id'])
            ->where('class_id', $data['class_id'])
            ->first();

        if (!$section) {
            return response()->json([
                'message' => 'Section does not belong to selected class'
            ], 422);
        }

        // Academic year lock
        $year = AcademicYear::findOrFail($academicYearId);
        if ($year->isReadOnly()) {
            return response()->json([
                'message' => 'Academic year is closed.'
            ], 403);
        }

        // Teacher validation
        $teacher = Teacher::where('id', $data['teacher_id'])
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'Invalid or inactive teacher'
            ], 422);
        }

        // Conflict check
        $exists = TeacherAssignment::where([
            'academic_year_id' => $academicYearId,
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'subject_id' => $data['subject_id'],
            'is_active' => true,
        ])->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Subject already assigned'
            ], 422);
        }

        $assignment = TeacherAssignment::create([
            'school_id' => $schoolId,
            'academic_year_id' => $academicYearId,
            'teacher_id' => $data['teacher_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'subject_id' => $data['subject_id'],
        ]);

        return response()->json($assignment, 201);
    }


    /**
     * List assignments
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = TeacherAssignment::with([
            'teacher',
            'class',
            'section',
            'subject'
        ]);

        if ($user->school_id) {
            $query->where('school_id', $user->school_id);
        } elseif ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        return response()->json(
            $query->orderBy('id', 'desc')->paginate(10)
        );
    }

    /**
     * Activate / deactivate assignment
     */
    public function toggleStatus($id)
    {
        $user = auth()->user();

        $assignment = TeacherAssignment::findOrFail($id);

        if ($user->school_id && $assignment->school_id !== $user->school_id) {
            abort(403);
        }

        // 🔒 Academic year lock (ADD THIS)
        $year = AcademicYear::findOrFail($assignment->academic_year_id);

        if ($year->isReadOnly()) {
            return response()->json([
                'message' => 'Academic year is closed. Teacher assignment cannot be modified.'
            ], 403);
        }

        $assignment->update([
            'is_active' => ! $assignment->is_active
        ]);

        return response()->json([
            'message' => $assignment->is_active
                ? 'Assignment activated'
                : 'Assignment deactivated'
        ]);
    }
}
