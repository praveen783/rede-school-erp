<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassSubjectController extends Controller
{
    /**
     * Get subjects assigned to a given class (school scoped)
     */
    public function index(Request $request, $classId)
    {
        $user = $request->user();

        $class = SchoolClass::where('school_id', $user->school_id)
            ->findOrFail($classId);

        $subjects = Subject::whereIn('id', function ($query) use ($classId) {
                $query->select('subject_id')
                      ->from('class_subjects')
                      ->where('class_id', $classId);
            })
            ->orderBy('name')
            ->get();

        return response()->json($subjects);
    }

    /**
     * Assign subjects to class (Add only new ones)
     */
    public function store(Request $request, $classId)
    {
        $user = $request->user();

        SchoolClass::where('school_id', $user->school_id)
            ->findOrFail($classId);

        $data = $request->validate([
            'subjects'   => 'required|array|min:1',
            'subjects.*' => 'exists:subjects,id'
        ]);

        $assigned = 0;

        foreach ($data['subjects'] as $subjectId) {

            $created = ClassSubject::firstOrCreate([
                'class_id'   => $classId,
                'subject_id' => $subjectId
            ]);

            if ($created->wasRecentlyCreated) {
                $assigned++;
            }
        }

        if ($assigned === 0) {
            return response()->json([
                'message' => 'Subjects already assigned'
            ]);
        }

        return response()->json([
            'message' => 'Subjects assigned successfully'
        ]);
    }

    /**
     * Smart update (Only apply differences)
     */
    public function update(Request $request, $classId)
    {
        $user = $request->user();

        SchoolClass::where('school_id', $user->school_id)
            ->findOrFail($classId);

        $data = $request->validate([
            'subjects'   => 'required|array',
            'subjects.*' => 'exists:subjects,id'
        ]);

        $existing = ClassSubject::where('class_id', $classId)
            ->pluck('subject_id')
            ->toArray();

        $incoming = $data['subjects'];

        $toAdd = array_diff($incoming, $existing);
        $toRemove = array_diff($existing, $incoming);

        foreach ($toAdd as $subjectId) {
            ClassSubject::create([
                'class_id'   => $classId,
                'subject_id' => $subjectId
            ]);
        }

        if (!empty($toRemove)) {
            ClassSubject::where('class_id', $classId)
                ->whereIn('subject_id', $toRemove)
                ->delete();
        }

        if (empty($toAdd) && empty($toRemove)) {
            return response()->json([
                'message' => 'No changes detected'
            ]);
        }

        return response()->json([
            'message' => 'Class subjects updated successfully',
            'added'   => array_values($toAdd),
            'removed' => array_values($toRemove)
        ]);
    }

    /**
     * Remove single subject from class
     */
    public function destroy(Request $request, $classId, $subjectId)
    {
        $user = $request->user();

        SchoolClass::where('school_id', $user->school_id)
            ->findOrFail($classId);

        ClassSubject::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->delete();

        return response()->json([
            'message' => 'Subject removed from class successfully'
        ]);
    }
}