<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Syllabus;
use App\Models\SyllabusUnit;
use App\Models\SyllabusResource;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyllabusController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX – List syllabi (Admin / Teacher / Student)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $user = auth()->user();

        $academicYear = AcademicYear::where('school_id', $user->school_id)
                            ->where('is_active', 1)
                            ->firstOrFail();

        $query = Syllabus::with(['board', 'class', 'subject'])
                    ->withCount(['units', 'resources'])
                    ->where('school_id', $user->school_id)
                    ->where('academic_year_id', $academicYear->id);

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        $syllabi = $query->latest()->get();

        return response()->json([
            'data' => $syllabi
        ]);
    }

    /*
|--------------------------------------------------------------------------
| SHOW – Single Syllabus Details (For Manage Page)
|--------------------------------------------------------------------------
*/
    public function show($id)
    {
        $user = auth()->user();

        // Get active academic year for safety
        $academicYear = AcademicYear::where('school_id', $user->school_id)
                            ->where('is_active', 1)
                            ->firstOrFail();

        // Fetch syllabus with relations
        $syllabus = Syllabus::with([
                            'board',
                            'class',
                            'subject',
                            'units' => function ($query) {
                                $query->orderBy('unit_order', 'asc');
                            },
                            'resources'
                        ])
                        ->where('school_id', $user->school_id)
                        ->where('academic_year_id', $academicYear->id)
                        ->findOrFail($id);

        return response()->json([
            'data' => $syllabus
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE – Create syllabus
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'board_id'   => 'required|exists:boards,id',
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title'      => 'required|string|max:255',
        ]);

        $academicYear = AcademicYear::where('school_id', $user->school_id)
                            ->where('is_active', 1)
                            ->firstOrFail();

        try {

            $syllabus = Syllabus::create([
                'school_id'        => $user->school_id,
                'academic_year_id' => $academicYear->id,
                'board_id'         => $request->board_id,
                'class_id'         => $request->class_id,
                'subject_id'       => $request->subject_id,
                'title'            => $request->title,
                'description'      => $request->description,
                'created_by'       => $user->id,
            ]);

            return response()->json([
                'message' => 'Syllabus created successfully',
                'data'    => $syllabus
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Syllabus already exists for this class and subject'
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $syllabus = Syllabus::where('school_id', $user->school_id)
                        ->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $syllabus->update([
            'title'       => $request->title,
            'description' => $request->description,
            'is_active'   => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Syllabus updated successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $user = auth()->user();

        $syllabus = Syllabus::where('school_id', $user->school_id)
                        ->findOrFail($id);

        $syllabus->delete();

        return response()->json([
            'message' => 'Syllabus deleted successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS-WISE SYLLABUS
    |--------------------------------------------------------------------------
    */
    public function classWise($classId)
    {
        $user = auth()->user();

        $academicYear = AcademicYear::where('school_id', $user->school_id)
                            ->where('is_active', 1)
                            ->firstOrFail();

        $syllabi = Syllabus::with(['subject', 'units', 'resources'])
                    ->where('school_id', $user->school_id)
                    ->where('academic_year_id', $academicYear->id)
                    ->where('class_id', $classId)
                    ->active()
                    ->get();

        return response()->json([
            'data' => $syllabi
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS + SUBJECT SYLLABUS
    |--------------------------------------------------------------------------
    */
    public function classSubjectSyllabus($classId, $subjectId)
    {
        $user = auth()->user();

        $academicYear = AcademicYear::where('school_id', $user->school_id)
                            ->where('is_active', 1)
                            ->firstOrFail();

        $syllabus = Syllabus::with([
                        'board',
                        'units' => function ($query) {
                            $query->orderBy('unit_order', 'asc');
                        },
                        'resources'
                    ])
                    ->where('school_id', $user->school_id)
                    ->where('academic_year_id', $academicYear->id)
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->active()
                    ->first();

        if (!$syllabus) {
            return response()->json([
                'message' => 'Syllabus not found'
            ], 404);
        }

        return response()->json([
            'data' => $syllabus
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADD UNIT (Auto Order)
    |--------------------------------------------------------------------------
    */
    public function addUnit(Request $request, $id)
    {
        $request->validate([
            'unit_title' => 'required|string|max:255',
            'learning_outcomes' => 'nullable|string',
            'estimated_hours' => 'nullable|integer'
        ]);

        // Get max unit_order for this syllabus
        $maxOrder = SyllabusUnit::where('syllabus_id', $id)
                        ->max('unit_order');

        $nextOrder = $maxOrder ? $maxOrder + 1 : 1;

        $unit = SyllabusUnit::create([
            'syllabus_id'       => $id,
            'unit_title'        => $request->unit_title,
            'unit_order'        => $nextOrder,
            'learning_outcomes' => $request->learning_outcomes,
            'estimated_hours'   => $request->estimated_hours,
        ]);

        return response()->json([
            'message' => 'Unit added successfully',
            'data'    => $unit
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE UNIT
    |--------------------------------------------------------------------------
    */
    public function updateUnit(Request $request, $unitId)
    {
        $unit = SyllabusUnit::findOrFail($unitId);

        $unit->update($request->only([
            'unit_title',
            'unit_order',
            'learning_outcomes',
            'estimated_hours',
            'is_completed'
        ]));

        return response()->json([
            'message' => 'Unit updated successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE UNIT
    |--------------------------------------------------------------------------
    */
    public function deleteUnit($unitId)
    {
        $unit = SyllabusUnit::findOrFail($unitId);
        $unit->delete();

        return response()->json([
            'message' => 'Unit deleted successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADD RESOURCE (Basic version – file upload later)
    |--------------------------------------------------------------------------
    */
    public function addResource(Request $request, $id)
    {
        $user = auth()->user();

        $request->validate([
            'resource_type'  => 'required|in:pdf,document,link,video',
            'resource_title' => 'required|string|max:255',
            'file'           => 'required_if:resource_type,pdf,document|file|mimes:pdf,doc,docx|max:51200',
            'resource_path'  => 'required_if:resource_type,link,video|url'
        ]);

        $path = null;

        // ===============================
        // File Upload Handling
        // ===============================
        if ($request->hasFile('file')) {

            $file = $request->file('file');

            // Generate unique file name
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Store inside storage/app/public/syllabus
            $path = $file->storeAs('syllabus', $filename, 'public');
        }

        // ===============================
        // External Link Handling
        // ===============================
        if (in_array($request->resource_type, ['link', 'video'])) {
            $path = $request->resource_path;
        }

        $resource = SyllabusResource::create([
            'syllabus_id'    => $id,
            'resource_type'  => $request->resource_type,
            'resource_title' => $request->resource_title,
            'resource_path'  => $path,
            'uploaded_by'    => $user->id,
        ]);

        return response()->json([
            'message' => 'Resource added successfully',
            'data'    => $resource
        ]);
    }
    
    /*
    |--------------------------------------------------------------------------
    | DELETE RESOURCE
    |--------------------------------------------------------------------------
    */
    public function deleteResource($resourceId)
    {
        $resource = SyllabusResource::findOrFail($resourceId);

        if (in_array($resource->resource_type, ['pdf', 'document'])) {
            Storage::disk('public')->delete($resource->resource_path);
        }

        $resource->delete();

        return response()->json([
            'message' => 'Resource deleted successfully'
        ]);
    }
}