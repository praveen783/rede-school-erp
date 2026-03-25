<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class TeacherAttendanceController extends Controller
{
    /**
     * Mark attendance for a class & section
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // ================================
        // Validate request
        // ================================
        $data = $request->validate([
            'class_id'              => 'required|integer',
            'section_id'            => 'required|integer',
            'attendance_date'       => 'required|date|in:' . now()->toDateString(),
            'records'               => 'required|array|min:1',
            'records.*.student_id'  => 'required|integer',
            'records.*.status'      => 'required|in:present,absent',
        ]);

        $isAdmin = in_array($user->role, ['school_admin', 'super_admin']);

        // Resolve teacher (if teacher login)
        $teacher = null;
        if ($user->role === 'teacher') {
            $teacher = Teacher::where('user_id', $user->id)
                ->where('school_id', $user->school_id)
                ->where('is_active', true)
                ->first();
        }

        // Resolve active academic year
        $academicYear = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            return response()->json([
                'message' => 'No active academic year found'
            ], 422);
        }

        // ================================
        // Class Teacher Authorization
        // ================================
        $isClassTeacher = false;

        if ($teacher) {
            $isClassTeacher = TeacherAssignment::where([
                'school_id'        => $user->school_id,
                'teacher_id'       => $teacher->id,
                'class_id'         => $data['class_id'],
                'section_id'       => $data['section_id'],
                'academic_year_id' => $academicYear->id,
                'is_class_teacher' => 1,
                'is_active'        => 1
            ])->exists();
        }

        if (! $isAdmin && ! $isClassTeacher) {
            return response()->json([
                'message' => 'Only class teacher or admin can take attendance'
            ], 403);
        }

        // ================================
        // Prevent duplicate attendance
        // ================================
        $alreadyMarked = Attendance::where([
            'school_id'        => $user->school_id,
            'academic_year_id' => $academicYear->id,
            'class_id'         => $data['class_id'],
            'section_id'       => $data['section_id'],
            'attendance_date'  => $data['attendance_date'],
        ])->exists();

        if ($alreadyMarked) {
            return response()->json([
                'message' => 'Attendance already marked for this date'
            ], 422);
        }

        // ================================
        // Store attendance
        // ================================
        foreach ($data['records'] as $record) {

            $student = Student::where([
                'id'         => $record['student_id'],
                'school_id'  => $user->school_id,
                'class_id'   => $data['class_id'],
                'section_id' => $data['section_id'],
                'is_active'  => true,
            ])->first();

            if (! $student) {
                continue;
            }

            Attendance::create([
                'school_id'        => $user->school_id,
                'academic_year_id' => $academicYear->id,
                'class_id'         => $data['class_id'],
                'section_id'       => $data['section_id'],
                'student_id'       => $student->id,
                'attendance_date'  => $data['attendance_date'],
                'status'           => $record['status'],
                'marked_by'        => $user->id,
                'marked_role'      => $user->role,
            ]);
        }

        return response()->json([
            'message' => 'Attendance marked successfully'
        ], 201);
    }

    /**
     * View attendance for a class, section & date
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'class_id'        => 'required|integer',
            'section_id'      => 'required|integer',
            'attendance_date' => 'required|date',
        ]);

        $isAdmin = in_array($user->role, ['school_admin', 'super_admin']);

        // Resolve teacher
        $teacher = null;
        if ($user->role === 'teacher') {
            $teacher = Teacher::where('user_id', $user->id)
                ->where('school_id', $user->school_id)
                ->where('is_active', true)
                ->first();
        }

        // Resolve academic year
        $academicYear = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            return response()->json([
                'message' => 'No active academic year found'
            ], 422);
        }

        // ================================
        // Authorization
        // ================================
        $isClassTeacher = false;

        if ($teacher) {
            $isClassTeacher = TeacherAssignment::where([
                'school_id'        => $user->school_id,
                'teacher_id'       => $teacher->id,
                'class_id'         => $request->class_id,
                'section_id'       => $request->section_id,
                'academic_year_id' => $academicYear->id,
                'is_class_teacher' => 1,
                'is_active'        => 1
            ])->exists();
        }

        if (! $isAdmin && ! $isClassTeacher) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        // ================================
        // Fetch attendance
        // ================================
        $attendance = Attendance::with('student:id,name,admission_no')
            ->where([
                'school_id'       => $user->school_id,
                'class_id'        => $request->class_id,
                'section_id'      => $request->section_id,
                'attendance_date' => $request->attendance_date,
            ])
            ->get();

        return response()->json($attendance);
    }
}