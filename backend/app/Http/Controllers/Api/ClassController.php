<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\AcademicYear;

class ClassController extends Controller
{
    
    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->school_id) {
            return response()->json([
                'message' => 'Unauthorized or school not assigned'
            ], 403);
        }

        $schoolId = $user->school_id;

        // ==============================
        // VALIDATION
        // ==============================
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes')->where(function ($query) use ($schoolId) {
                    return $query->where('school_id', $schoolId);
                })
            ],
            'sections' => 'required|array|min:1',
            'sections.*' => 'required|string|max:10'
        ]);

        // ==============================
        // GET ACTIVE ACADEMIC YEAR
        // ==============================
        $academicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if (! $academicYear) {
            return response()->json([
                'message' => 'No active academic year found'
            ], 422);
        }

        // ==============================
        // CLEAN & UNIQUE SECTION NAMES
        // ==============================
        $sections = collect($validated['sections'])
            ->map(fn ($s) => strtoupper(trim($s)))
            ->unique()
            ->values();

        DB::beginTransaction();

        try {

            // ==============================
            // 1️⃣ CREATE CLASS
            // ==============================
            $class = SchoolClass::create([
                'school_id' => $schoolId,
                'name' => $validated['name']
            ]);

            // ==============================
            // 2️⃣ CREATE SECTIONS + PIVOT
            // ==============================
            foreach ($sections as $sectionName) {

                $section = Section::create([
                    'class_id' => $class->id,
                    'name' => $sectionName
                ]);

                DB::table('class_sections')->insert([
                    'school_id'        => $schoolId,
                    'academic_year_id' => $academicYear->id,
                    'class_id'         => $class->id,
                    'section_id'       => $section->id,
                    'status'           => 'active',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Class created successfully',
                'class'   => $class
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create class',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

 
   public function index()
    {
        $schoolId = auth()->user()->school_id;

        return response()->json([
            'data' => SchoolClass::where('school_id', $schoolId)
                ->orderByRaw("
                    CASE
                        WHEN name = 'Nursery' THEN 0
                        WHEN name = 'LKG' THEN 1
                        WHEN name = 'UKG' THEN 2
                        ELSE CAST(name AS UNSIGNED)
                    END
                ")
                ->get(['id', 'name'])
        ]);
    }


}
