<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Period;
use App\Models\TimetableEntry;

class PeriodController extends Controller
{
    /**
     * Display all periods (school-wise)
     */
    public function index()
    {
        $periods = Period::where('school_id', auth()->user()->school_id)
            ->orderBy('order')
            ->get();

        return response()->json($periods);
    }

    /**
     * Store a new period
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'is_break'   => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
        ]);

        $schoolId = auth()->user()->school_id;

        //  Prevent overlapping time
        $this->validateOverlap($request->start_time, $request->end_time, $schoolId);

        //  Auto-generate order
        $maxOrder = Period::where('school_id', $schoolId)->max('order') ?? 0;

        $period = Period::create([
            'school_id'  => $schoolId,
            'name'       => $request->name,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'order'      => $maxOrder + 1,
            'is_break'   => $request->is_break ?? false,
            'is_active'  => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Period created successfully.',
            'data'    => $period
        ], 201);
    }

    /**
     * Update period
     */
    public function update(Request $request, Period $period)
    {
        $this->authorizeSchool($period);

        $request->validate([
            'name'       => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'is_break'   => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
        ]);

        // 🔒 Prevent editing if period is used
        $this->preventIfUsed($period);

        // 🔴 Prevent overlapping (excluding current)
        $this->validateOverlap(
            $request->start_time,
            $request->end_time,
            $period->school_id,
            $period->id
        );

        $period->update([
            'name'       => $request->name,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'is_break'   => $request->is_break ?? false,
            'is_active'  => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Period updated successfully.',
            'data'    => $period
        ]);
    }

    /**
     * Delete period
     */
    public function destroy(Period $period)
    {
        $this->authorizeSchool($period);

        // 🔒 Prevent delete if used
        $this->preventIfUsed($period);

        $period->delete();

        return response()->json([
            'message' => 'Period deleted successfully.'
        ]);
    }

    /**
     * Reorder periods (Drag & Drop support)
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'periods' => 'required|array'
        ]);

        foreach ($request->periods as $item) {
            Period::where('id', $item['id'])
                ->where('school_id', auth()->user()->school_id)
                ->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Periods reordered successfully.'
        ]);
    }

    /* ======================================================
       =============== PRIVATE HELPER METHODS ===============
       ====================================================== */

    /**
     * Prevent overlapping periods
     */
    private function validateOverlap($start, $end, $schoolId, $excludeId = null)
    {
        $query = Period::where('school_id', $schoolId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_time', [$start, $end])
              ->orWhereBetween('end_time', [$start, $end])
              ->orWhere(function ($sub) use ($start, $end) {
                  $sub->where('start_time', '<=', $start)
                      ->where('end_time', '>=', $end);
              });
        })->exists();

        if ($exists) {
            abort(422, 'Period time overlaps with an existing period.');
        }
    }

    /**
     * Prevent modification if used in timetable
     */
    private function preventIfUsed(Period $period)
    {
        $used = TimetableEntry::where('period_id', $period->id)->exists();

        if ($used) {
            abort(422, 'Cannot modify period. It is already used in timetable.');
        }
    }

    /**
     * Ensure same school access
     */
    private function authorizeSchool(Period $period)
    {
        if ($period->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}