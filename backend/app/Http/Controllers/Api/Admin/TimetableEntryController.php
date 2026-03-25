<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;

class TimetableEntryController extends Controller
{
    public function store(Request $request, Timetable $timetable)
    {
        $request->validate([
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        // 1️⃣ Prevent duplicate entry in same timetable (same day + period)
        $existing = TimetableEntry::where('timetable_id', $timetable->id)
            ->where('day_of_week', $request->day_of_week)
            ->where('period_id', $request->period_id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'This period is already assigned.'
            ], 422);
        }

        // 2️⃣ Validate teacher teaches this subject in this class
        $validAssignment = TeacherAssignment::where('academic_year_id', $timetable->academic_year_id)
            ->where('class_id', $timetable->class_id)
            ->where('teacher_id', $request->teacher_id)
            ->where('subject_id', $request->subject_id)
            ->where('is_active', 1)
            ->when($timetable->section_id, function ($q) use ($timetable) {
                $q->where('section_id', $timetable->section_id);
            })
            ->when(!$timetable->section_id, function ($q) {
                $q->whereNull('section_id');
            })
            ->exists();

        if (!$validAssignment) {
            return response()->json([
                'message' => 'Teacher is not assigned to this subject for this class.'
            ], 422);
        }

        // 3️⃣ Conflict Check: Teacher double booking
        // Ensure teacher is not assigned to two classes at the same day + period,
        // within the same school + active academic year + active timetable.
        $teacherConflict = TimetableEntry::where('day_of_week', $request->day_of_week)
            ->where('period_id', $request->period_id)
            ->where('teacher_id', $request->teacher_id)
            ->whereHas('timetable', function ($q) use ($timetable) {
                $q->where('school_id', $timetable->school_id)
                    ->where('academic_year_id', $timetable->academic_year_id)
                    ->where('is_active', true);
            })
            ->exists();

        if ($teacherConflict) {
            return response()->json([
                'message' => 'Teacher already assigned to another class at this time.'
            ], 422);
        }

        // 4️⃣ Create entry
        $entry = TimetableEntry::create([
            'timetable_id' => $timetable->id,
            'day_of_week' => $request->day_of_week,
            'period_id' => $request->period_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
        ]);

        return response()->json([
            'message' => 'Period assigned successfully.',
            'data' => $entry
        ], 201);
    }
    public function update(Request $request, TimetableEntry $entry)
    {
        $request->validate([
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $timetable = $entry->timetable;

        // 1️⃣ Prevent duplicate period in same timetable
        $duplicate = TimetableEntry::where('timetable_id', $timetable->id)
            ->where('day_of_week', $request->day_of_week)
            ->where('period_id', $request->period_id)
            ->where('id', '!=', $entry->id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'This period is already assigned.'
            ], 422);
        }

        // 2️⃣ Validate teacher assignment
        $validAssignment = TeacherAssignment::where('academic_year_id', $timetable->academic_year_id)
            ->where('class_id', $timetable->class_id)
            ->where('teacher_id', $request->teacher_id)
            ->where('subject_id', $request->subject_id)
            ->where('is_active', 1)
            ->when($timetable->section_id, function ($q) use ($timetable) {
                $q->where('section_id', $timetable->section_id);
            })
            ->when(!$timetable->section_id, function ($q) {
                $q->whereNull('section_id');
            })
            ->exists();

        if (!$validAssignment) {
            return response()->json([
                'message' => 'Teacher is not assigned to this subject for this class.'
            ], 422);
        }

        // 3️⃣ Teacher conflict check
        $teacherConflict = TimetableEntry::where('day_of_week', $request->day_of_week)
            ->where('period_id', $request->period_id)
            ->where('teacher_id', $request->teacher_id)
            ->where('id', '!=', $entry->id)
            ->whereHas('timetable', function ($q) use ($timetable) {
                $q->where('academic_year_id', $timetable->academic_year_id);
            })
            ->exists();

        if ($teacherConflict) {
            return response()->json([
                'message' => 'Teacher already assigned to another class at this time.'
            ], 422);
        }

        // 4️⃣ Update entry
        $entry->update([
            'day_of_week' => $request->day_of_week,
            'period_id' => $request->period_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
        ]);

        return response()->json([
            'message' => 'Timetable entry updated successfully.',
            'data' => $entry
        ]);
    }
    public function destroy(TimetableEntry $entry)
    {
        $entry->delete();

        return response()->json([
            'message' => 'Timetable entry deleted successfully.'
        ]);
    }
}
