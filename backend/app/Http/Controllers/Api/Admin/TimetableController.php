<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Models\Period;


class TimetableController extends Controller

{
    public function index()
    {
        $schoolId = auth()->user()->school_id;

        $timetables = Timetable::with([
                'academicYear:id,name',
                'schoolClass:id,name',
                'section:id,name'
            ])
            ->where('school_id', $schoolId)
            ->orderByDesc('created_at')
            ->get([
                'id',
                'school_id',
                'academic_year_id',
                'class_id',
                'section_id',
                'is_active',
                'created_at'
            ]);

        return response()->json($timetables);
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        // Prevent duplicate timetable
        $existing = Timetable::where('academic_year_id', $request->academic_year_id)
            ->where('class_id', $request->class_id)
            ->when($request->filled('section_id'), function ($q) use ($request) {
                $q->where('section_id', $request->section_id);
            })
            ->when(!$request->filled('section_id'), function ($q) {
                $q->whereNull('section_id');
            })
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Timetable already exists for this class and academic year.'
            ], 422);
        }

        $timetable = Timetable::create([
            'school_id' => auth()->user()->school_id,
            'academic_year_id' => $request->academic_year_id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Timetable created successfully.',
            'data' => $timetable
        ], 201);
    }

    public function show(Timetable $timetable)
    {
        // Security: Ensure same school
        if ($timetable->school_id !== auth()->user()->school_id) {
            return response()->json([
                'message' => 'Unauthorized access.'
            ], 403);
        }

        // Load relationships
        $timetable->load([
            'academicYear:id,name',
            'schoolClass:id,name',
            'section:id,name',
            'entries.period:id,name,start_time,end_time',
            'entries.subject:id,name',
            'entries.teacher:id,name'
        ]);

        // Fetch all active periods (ordered)
        $periods = Period::where('school_id', auth()->user()->school_id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Days (static)
        $days = [
            'Monday','Tuesday','Wednesday',
            'Thursday','Friday','Saturday'
        ];

        return response()->json([
            'timetable' => $timetable,
            'periods' => $periods,
            'days' => $days
        ]);
    }

    /**
     * Teacher: My timetable (by teacher user)
     */
    public function myTeacherTimetable()
    {
        $user = auth()->user();

        // Resolve teacher profile linked to this user
        $teacher = Teacher::where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Resolve active academic year for the school
        $academicYear = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Fetch timetable entries for this teacher
        $entries = TimetableEntry::with([
                'period:id,name,start_time,end_time',
                'subject:id,name',
                'timetable.schoolClass:id,name',
                'timetable.section:id,name'
            ])
            ->where('teacher_id', $teacher->id)
            ->whereHas('timetable', function ($q) use ($user, $academicYear) {
                $q->where('school_id', $user->school_id)
                    ->where('academic_year_id', $academicYear->id)
                    ->where('is_active', true);
            })
            ->get();

        // Load periods (same as class timetable view)
        $periods = Period::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Days (static)
        $days = [
            'Monday','Tuesday','Wednesday',
            'Thursday','Friday','Saturday'
        ];

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
                'employee_code' => $teacher->employee_code,
            ],
            'periods' => $periods,
            'days' => $days,
            'entries' => $entries,
        ]);
    }

    public function getClassAssignmentData(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id'
        ]);

        $query = TeacherAssignment::with(['teacher','subject'])
            ->where('academic_year_id', $request->academic_year_id)
            ->where('class_id', $request->class_id)
            ->whereNotNull('subject_id')
            ->where('is_active', 1);

        // Apply section filter only if provided
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $assignments = $query->get();

        // Extract unique subjects
        $subjects = $assignments
            ->pluck('subject')
            ->filter() // remove null safety
            ->unique('id')
            ->values();

        // Group teachers by subject
        $teachersBySubject = [];

        foreach ($assignments as $assignment) {
            $subjectId = $assignment->subject_id;

            if (!isset($teachersBySubject[$subjectId])) {
                $teachersBySubject[$subjectId] = [];
            }

            $teachersBySubject[$subjectId][] = [
                'id'   => $assignment->teacher->id,
                'name' => $assignment->teacher->name,
            ];
        }

    // Remove duplicate teachers per subject
        foreach ($teachersBySubject as $subjectId => $teachers) {
            $teachersBySubject[$subjectId] =
                collect($teachers)->unique('id')->values();
        }
        return response()->json([
            'subjects' => $subjects,
            'teachers_by_subject' => $teachersBySubject
        ]);
    }
}
