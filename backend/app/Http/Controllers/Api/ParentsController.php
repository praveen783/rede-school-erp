<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParentProfile;
use App\Models\Student;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ParentsController extends Controller
{
    /**
     * Display a listing of parents (school scoped)
     */
    public function index(Request $request)
    {
        $parents = ParentProfile::where('school_id', $request->user()->school_id)
            ->latest()
            ->get();

        return response()->json($parents);
    }

    /**
     * Store a newly created parent
     */
    public function store(Request $request)
    {
        // ================================
        // VALIDATION (extended)
        // ================================
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => [
                'nullable',
                'string',
                Rule::unique('parents')->where(fn ($q) =>
                    $q->where('school_id', $request->user()->school_id)
                ),
            ],
            'email'      => 'nullable|email',
            'occupation' => 'nullable|string|max:255',

            // 🔽 NEW (optional)
            'student_ids'   => 'nullable|array',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        $schoolId = $request->user()->school_id;

        // ================================
        // TRANSACTION (safe & atomic)
        // ================================
        $parent = DB::transaction(function () use ($data, $schoolId) {

            // 1️⃣ Create Parent
            $parent = ParentProfile::create([
                'school_id'  => $schoolId,
                'name'       => $data['name'],
                'phone'      => $data['phone'] ?? null,
                'email'      => $data['email'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'is_active'  => 1,
            ]);

            // 2️⃣ Attach Students (OPTIONAL)
            if (!empty($data['student_ids'])) {

                Student::whereIn('id', $data['student_ids'])
                    ->where('school_id', $schoolId) // 🔒 school isolation
                    ->update([
                        'parent_id'   => $parent->id,
                        'parent_name' => $parent->name, // keeps legacy column in sync
                    ]);
            }

            return $parent;
        });

        return response()->json([
            'message' => 'Parent created successfully',
            'data'    => $parent,
        ], 201);
    }

    /**
     * Display a single parent (with students)
     */
    public function show(Request $request, $id)
    {
        $parent = ParentProfile::where('school_id', $request->user()->school_id)
            ->with('students')
            ->findOrFail($id);

        return response()->json($parent);
    }

    /**
     * Update parent
     */
    public function update(Request $request, $id)
    {
        $parent = ParentProfile::where('school_id', $request->user()->school_id)
            ->findOrFail($id);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => [
                'nullable',
                'string',
                Rule::unique('parents')
                    ->ignore($parent->id)
                    ->where(fn ($q) =>
                        $q->where('school_id', $request->user()->school_id)
                    ),
            ],
            'email'      => 'nullable|email',
            'occupation' => 'nullable|string|max:255',
            'is_active'  => 'boolean',
        ]);

        $parent->update($data);

        return response()->json($parent);
    }

    /**
     * Soft delete parent
     */
    public function destroy(Request $request, $id)
    {
        $parent = ParentProfile::where('school_id', $request->user()->school_id)
            ->findOrFail($id);

        $parent->delete();

        return response()->json([
            'message' => 'Parent deleted successfully',
        ]);
    }
}
