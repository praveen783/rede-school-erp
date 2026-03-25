<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'admission_no' => 'required|string|unique:students,admission_no',
            'name' => 'required|string',
            'parent_name' => 'nullable|string',
            'address' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'category_id' => 'nullable|exists:categories,id',
            'dob' => 'nullable|date',
            'date_of_joining' => 'nullable|date',

        ]);

        // Academic year lock (ADD THIS)
        $year = AcademicYear::findOrFail($data['academic_year_id']);

        if ($year->isReadOnly()) {
            return response()->json([
                'message' => 'Academic year is closed. Students cannot be created.'
            ], 403);
        }

        $data['user_id'] = null;

        $student = Student::create($data);

        return response()->json($student, 201);
    }


    public function index(Request $request)
    {
        $query = Student::with([
            'class:id,name',
            'section:id,name',
            'academicYear:id,name'
        ]);

        // Restrict to logged-in user's school
        if (auth()->user()->school_id) {
            $query->where('school_id', auth()->user()->school_id);
        }

        // Filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // SEARCH FILTER
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                ->orWhere('parent_name', 'like', "%{$search}%")
                ->orWhere('admission_no', 'like', "%{$search}%");

            });
        }

        // Pagination with query preservation
        $students = $query
            ->orderByDesc('is_active')
            ->orderBy('date_of_joining')
            ->orderBy('name')
            ->paginate($request->get('per_page', 10))
            ->appends($request->query());

        return response()->json($students);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        // Academic year lock (ADD THIS)
        $year = AcademicYear::findOrFail($student->academic_year_id);

        if ($year->isReadOnly()) {
            return response()->json([
                'message' => 'Academic year is closed. Students cannot be updated.'
            ], 403);
        }

        $data = $request->validate([
            'school_id' => 'sometimes|exists:schools,id',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'class_id' => 'sometimes|exists:classes,id',
            'section_id' => 'sometimes|exists:sections,id',
            'admission_no' => 'sometimes|string|unique:students,admission_no,' . $student->id,
            'name' => 'sometimes|string',
            'gender' => 'sometimes|in:male,female',
            'dob' => 'sometimes|date',
            'date_of_joining' => 'sometimes|date',
            'is_active' => 'sometimes|boolean',
        ]);

        $student->update($data);

        return response()->json($student);
    }

    public function deactivate($id)
    {
        $student = Student::findOrFail($id);

        // Academic year lock (ADD THIS)
        $year = AcademicYear::findOrFail($student->academic_year_id);

        if ($year->isReadOnly()) {
            return response()->json([
                'message' => 'Academic year is closed. Student status cannot be changed.'
            ], 403);
        }

        $student->update([
            'is_active' => false
        ]);

        return response()->json([
            'message' => 'Student deactivated successfully'
        ]);
    }

    
    public function activate($id)
    {
        $student = Student::findOrFail($id);

        // Academic year lock (ADD THIS)
        $year = AcademicYear::findOrFail($student->academic_year_id);

        if ($year->isReadOnly()) {
            return response()->json([
                'message' => 'Academic year is closed. Student status cannot be changed.'
            ], 403);
        }

        $student->update([
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Student activated successfully'
        ]);
    }


    public function promote(Request $request)
    {
        $data = $request->validate([
            'from_academic_year_id' => 'required|exists:academic_years,id',
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'from_class_id' => 'required|exists:classes,id',
            'to_class_id' => 'required|exists:classes,id',
            'from_section_id' => 'required|exists:sections,id',
            'to_section_id' => 'required|exists:sections,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $fromYear = AcademicYear::findOrFail($data['from_academic_year_id']);
        $toYear   = AcademicYear::findOrFail($data['to_academic_year_id']);

        if (! $fromYear->isClosed()) {
            return response()->json([
                'message' => 'Source academic year must be closed'
            ], 422);
        }

        if (! $toYear->isActive()) {
            return response()->json([
                'message' => 'Target academic year must be active'
            ], 422);
        }

        // Track results
        $promoted = [];
        $skipped  = [];

        DB::transaction(function () use ($data, &$promoted, &$skipped) {

            foreach ($data['student_ids'] as $studentId) {

                $student = Student::where('id', $studentId)
                    ->where('academic_year_id', $data['from_academic_year_id'])
                    ->where('class_id', $data['from_class_id'])
                    ->where('section_id', $data['from_section_id'])
                    ->where('is_active', true)
                    ->first();

                if (! $student) {
                    $skipped[] = [
                        'student_id' => $studentId,
                        'reason' => 'Student not eligible for promotion'
                    ];
                    continue;
                }

                //  Prevent duplicate promotion
                $alreadyPromoted = Student::where('academic_year_id', $data['to_academic_year_id'])
                    ->where('admission_no', $student->admission_no)
                    ->exists();

                if ($alreadyPromoted) {
                    $skipped[] = [
                        'student_id' => $student->id,
                        'reason' => 'Student already promoted'
                    ];
                    continue;
                }

                Student::create([
                    'school_id' => $student->school_id,
                    'academic_year_id' => $data['to_academic_year_id'],
                    'class_id' => $data['to_class_id'],
                    'section_id' => $data['to_section_id'],
                    'admission_no' => $student->admission_no,
                    'name' => $student->name,
                    'gender' => $student->gender,
                    'dob' => $student->dob,
                    'is_active' => true,
                ]);

                $promoted[] = $student->id;
            }
        });

        return response()->json([
            'message' => 'Student promotion completed',
            'promoted_count' => count($promoted),
            'skipped_count'  => count($skipped),
            'skipped'        => $skipped,
        ]);
    }
    public function show($id)
    {
        $student = Student::with(['class', 'section', 'academicYear'])
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($id);

        return response()->json($student);
    }



}
