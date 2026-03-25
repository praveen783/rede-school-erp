<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;

class AdminStudentController extends Controller
{
    /**
     * Display full student listing (paginated)
     * Optional usage for admin student page
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $students = Student::with(['class', 'section', 'academicYear'])
            ->where('school_id', $user->school_id)
            ->where('is_active', 1)

            // Search by name or admission number
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('admission_no', 'like', "%{$request->search}%");
                });
            })

            // Optional filters
            ->when($request->class_id, function ($query) use ($request) {
                $query->where('class_id', $request->class_id);
            })

            ->when($request->section_id, function ($query) use ($request) {
                $query->where('section_id', $request->section_id);
            })

            ->orderBy('name')
            ->paginate(10);

        return response()->json($students);
    }

    /**
     * Lightweight student options endpoint
     * Used for dropdown selectors (Select2 etc.)
     */
    public function options(Request $request)
    {
        $user = auth()->user();

        $students = Student::with(['class', 'section'])
            ->where('school_id', $user->school_id)
            ->where('is_active', 1)

            // Search by name or admission number
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('admission_no', 'like', "%{$request->search}%");
                });
            })

            // Optional filters
            ->when($request->class_id, function ($query) use ($request) {
                $query->where('class_id', $request->class_id);
            })

            ->when($request->section_id, function ($query) use ($request) {
                $query->where('section_id', $request->section_id);
            })

            ->orderBy('name')
            ->limit(20) // prevent heavy queries
            ->get();

        return response()->json(
            $students->map(function ($student) {

                $className = $student->class->name ?? '';
                $sectionName = $student->section->name ?? '';

                return [
                    'id' => $student->id,
                    'text' => "{$student->name} ({$student->admission_no}) - {$className} {$sectionName}"
                ];
            })
        );
    }
}