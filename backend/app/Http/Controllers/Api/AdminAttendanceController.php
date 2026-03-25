<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAttendanceController extends Controller
{
    /**
     * Load attendance (Admin)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'class_id'        => 'required|integer',
            'section_id'      => 'required|integer',
            'attendance_date' => 'required|date',
        ]);

        $academicYear = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Get students
        $students = Student::where([
                'school_id' => $user->school_id,
                'class_id'  => $data['class_id'],
                'section_id'=> $data['section_id'],
                'is_active' => true,
            ])
            ->orderBy('admission_no')
            ->get();

        // Get attendance records
        $attendance = Attendance::where([
                'school_id'        => $user->school_id,
                'academic_year_id' => $academicYear->id,
                'class_id'         => $data['class_id'],
                'section_id'       => $data['section_id'],
                'attendance_date'  => $data['attendance_date'],
            ])
            ->get()
            ->keyBy('student_id');

        $response = $students->map(function ($student) use ($attendance) {
            return [
                'student_id'  => $student->id,
                'name'        => $student->name,
                'admission_no' => $student->admission_no,
                'status'      => $attendance[$student->id]->status ?? null,
            ];
        });

        return response()->json([
            'attendance_date' => $data['attendance_date'],
            'already_marked'  => $attendance->count() > 0,
            'students'        => $response,
        ]);
    }

    /**
     * Store or Update attendance (Admin)
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'class_id'              => 'required|integer',
            'section_id'            => 'required|integer',
            'attendance_date'       => 'required|date',
            'records'               => 'required|array|min:1',
            'records.*.student_id'  => 'required|integer',
            'records.*.status'      => 'required|in:present,absent',
        ]);

        $academicYear = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        if ($academicYear->isReadOnly()) {
            return response()->json([
                'message' => 'Academic year is closed. Attendance cannot be modified.'
            ], 403);
        }

        DB::transaction(function () use ($data, $user, $academicYear) {

            foreach ($data['records'] as $record) {

                $student = Student::where([
                    'id'         => $record['student_id'],
                    'school_id'  => $user->school_id,
                    'class_id'   => $data['class_id'],
                    'section_id' => $data['section_id'],
                    'is_active'  => true,
                ])->first();

                if (! $student) continue;

                Attendance::updateOrCreate(
                    [
                        'school_id'        => $user->school_id,
                        'academic_year_id' => $academicYear->id,
                        'class_id'         => $data['class_id'],
                        'section_id'       => $data['section_id'],
                        'student_id'       => $student->id,
                        'attendance_date'  => $data['attendance_date'],
                    ],
                    [
                        'status'    => $record['status'],
                        'marked_by' => $user->id,
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Attendance saved successfully.'
        ]);
    }

    public function monitor(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'attendance_date' => 'required|date'
        ]);

        $date = $request->attendance_date;

        if ($date > now()->toDateString()) {
            return response()->json([
                'message' => 'Future attendance monitoring not allowed'
            ], 422);
        }

        // 🔥 ACTIVE ACADEMIC YEAR
        $academicYear = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        // 🔹 Fetch classes + sections
        $classSections = DB::table('sections')
            ->join('classes', 'classes.id', '=', 'sections.class_id')
            ->where('classes.school_id', $user->school_id)
            ->select(
                'classes.id as class_id',
                'classes.name as class_name',
                'sections.id as section_id',
                'sections.name as section_name'
            )
            ->get();

        $response = $classSections->map(function ($item) use ($user, $date, $academicYear) {

            $attendance = Attendance::where([
                'school_id'        => $user->school_id,
                'academic_year_id' => $academicYear->id,
                'class_id'         => $item->class_id,
                'section_id'       => $item->section_id,
                'attendance_date'  => $date,
            ])->first();

            if ($attendance) {
                return [
                    'class_id'      => $item->class_id,
                    'class_name'    => $item->class_name,
                    'section_id'    => $item->section_id,
                    'section_name'  => $item->section_name,
                    'status'        => 'marked',
                    'marked_role'   => $attendance->marked_role ?? '-',
                    'total_records' => Attendance::where([
                        'school_id'        => $user->school_id,
                        'academic_year_id' => $academicYear->id,
                        'class_id'         => $item->class_id,
                        'section_id'       => $item->section_id,
                        'attendance_date'  => $date,
                    ])->count(),
                ];
            }

            return [
                'class_id'      => $item->class_id,
                'class_name'    => $item->class_name,
                'section_id'    => $item->section_id,
                'section_name'  => $item->section_name,
                'status'        => 'not_marked',
                'marked_role'   => null,
                'total_records' => 0,
            ];
        });

        return response()->json($response);
    }

}