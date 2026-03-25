<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use App\Services\TeacherPermissionService;
use App\Enums\Role;
use App\Models\AcademicYear;

class AttendanceController extends Controller
{
    public function mark(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'attendance_date' => 'required|date',
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.status' => 'required|in:present,absent',
        ]);

        $year = AcademicYear::findOrFail($data['academic_year_id']);

        if ($year->isReadOnly()) {
            return response()->json([
                'message' => 'Academic year is closed. Attendance is read-only.'
            ], 403);
        }

        $marked = [];
        $skipped = [];

        // TEACHER permission check (ADD HERE)
        $user = auth()->user();

        if ($user->role === Role::TEACHER) {

            $allowed = TeacherPermissionService::isAssigned(
                $user->id,
                $data['academic_year_id'],
                $data['class_id'],
                $data['section_id'],
                /* subject_id not applicable for attendance */
                /* use 0 or skip subject check if attendance is class-level */
                0
            );

            if (! $allowed) {
                return response()->json([
                    'message' => 'You are not allowed to mark attendance for this class'
                ], 403);
            }
        }


        foreach ($data['students'] as $item) {

            // Check student is active
            $student = Student::where('id', $item['student_id'])
                ->where('is_active', true)
                ->first();

            if (!$student) {
                $skipped[] = [
                    'student_id' => $item['student_id'],
                    'reason' => 'Student inactive or not found'
                ];
                continue;
            }

            // Prevent duplicate attendance
            $exists = Attendance::where('student_id', $item['student_id'])
                ->where('attendance_date', $data['attendance_date'])
                ->exists();

            if ($exists) {
                $skipped[] = [
                    'student_id' => $item['student_id'],
                    'reason' => 'Attendance already marked'
                ];
                continue;
            }

            $attendance = Attendance::create([
                'school_id' => $data['school_id'],
                'academic_year_id' => $data['academic_year_id'],
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'student_id' => $item['student_id'],
                'attendance_date' => $data['attendance_date'],
                'status' => $item['status'],
            ]);

            $marked[] = $attendance;
        }

        return response()->json([
            'message' => 'Attendance marking completed',
            'marked_count' => count($marked),
            'skipped_count' => count($skipped),
            'skipped' => $skipped,
        ]);
    }
    public function index(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'nullable|date',
        ]);

        $query = Attendance::with('student')
            ->where('school_id', $request->school_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id);

        if ($request->filled('date')) {
            $query->where('attendance_date', $request->date);
        }

        return response()->json(
            $query->orderBy('attendance_date')->get()
        );
    }
    
    public function summary(Request $request)
    {
        $request->validate([
            'school_id' => 'required|integer',
            'academic_year_id' => 'required|integer',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $data = Attendance::query()
            ->where('school_id', $request->school_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('attendance_date', $request->date)
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(status = 'present') as present"),
                DB::raw("SUM(status = 'absent') as absent"),
            ])
            ->first();

        return response()->json([
            'total'   => (int) ($data->total ?? 0),
            'present'=> (int) ($data->present ?? 0),
            'absent' => (int) ($data->absent ?? 0),
        ]);
    }
    public function studentSummary()
    {
        $user = auth()->user();

        $student = Student::where('user_id', $user->id)->firstOrFail();

        $academicYear = AcademicYear::where('school_id', $student->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        $total = Attendance::where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->count();

        $present = Attendance::where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('status', 'present')
            ->count();

        $percentage = $total > 0 ? round(($present / $total) * 100) : 0;

        return response()->json([
            'total_days' => $total,
            'present_days' => $present,
            'attendance_percentage' => $percentage
        ]);
    }
    public function studentAttendance()
    {
        $user = auth()->user();

        // Resolve student safely
        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Active academic year
        $academicYear = AcademicYear::where('school_id', $student->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        $records = Attendance::where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->orderByDesc('attendance_date')
            ->get([
                'attendance_date',
                'status'
            ]);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
            ],
            'attendance' => $records
        ]);
    }



}
