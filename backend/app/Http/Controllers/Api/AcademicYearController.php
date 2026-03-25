<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;

class AcademicYearController extends Controller
{
    
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->school_id) {
            return response()->json([
                'message' => 'School not assigned to user'
            ], 403);
        }

        $years = AcademicYear::where('school_id', $user->school_id)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($year) {
                return [
                    'id'         => $year->id,
                    'school_id'  => $year->school_id,
                    'name'       => $year->name,
                    'start_date' => $year->start_date,
                    'end_date'   => $year->end_date,
                    'is_active'  => (bool) $year->is_active,
                    'status'     => $year->is_active ? 'active' : 'archived',
                    'created_at' => $year->created_at,
                    'updated_at' => $year->updated_at,
                ];
            });

        return response()->json($years);
    }


    /**
     * POST /api/academic-years
     * Create & activate new academic year
     */
    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'name'       => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        // Deactivate existing active year
        AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $year = AcademicYear::create([
            'school_id' => $schoolId,
            'is_active' => true,
            ...$data
        ]);

        return response()->json($year, 201);
    }

    /**
     * POST /api/academic-years/{id}/close
     */
    public function close($id)
    {
        $year = AcademicYear::where('school_id', auth()->user()->school_id)
            ->findOrFail($id);

        if (! $year->is_active) {
            return response()->json([
                'message' => 'Only active academic year can be closed'
            ], 422);
        }

        $year->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Academic year closed successfully'
        ]);
    }

    public function activate($id)
    {
        $schoolId = auth()->user()->school_id;

        $year = AcademicYear::where('school_id', $schoolId)
            ->findOrFail($id);

        if ($year->is_active) {
            return response()->json([
                'message' => 'Academic year is already active'
            ], 422);
        }

        // Deactivate current active year
        AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Activate selected year
        $year->update([
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Academic year activated successfully'
        ]);
    }
}
