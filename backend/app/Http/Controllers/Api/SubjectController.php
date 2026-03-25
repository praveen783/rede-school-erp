<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * Store a new subject (Global Subject Master)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->school_id) {
            return response()->json([
                'message' => 'Unauthorized or school not assigned'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        // Normalize subject name
        $subjectName = trim($validated['name']);

        // Prevent duplicate subject name within same school
        $exists = Subject::where('school_id', $user->school_id)
            ->whereRaw('LOWER(name) = ?', [strtolower($subjectName)])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Subject already exists for this school'
            ], 422);
        }

        $subject = Subject::create([
            'school_id' => $user->school_id,
            'name'      => $subjectName,
            'code'      => $validated['code'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Subject created successfully',
            'subject' => $subject
        ], 201);
    }

    /**
     * List subjects (school scoped)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->school_id) {
            return response()->json([
                'message' => 'Unauthorized or school not assigned'
            ], 403);
        }

        $query = Subject::where('school_id', $user->school_id);

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }

    /**
     * Deactivate subject (Soft deactivate)
     */
    public function deactivate(Request $request, $id)
    {
        $user = $request->user();

        $subject = Subject::where('school_id', $user->school_id)
            ->findOrFail($id);

        $subject->update([
            'is_active' => false
        ]);

        return response()->json([
            'message' => 'Subject deactivated successfully'
        ]);
    }
}