<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Mark;
use Illuminate\Http\Request;

class AdminResultController extends Controller
{
    /**
     * Publish exam results (LOCK MARKS)
     */
    public function publish($examId)
    {
        $admin = auth()->user();

        $exam = Exam::where('id', $examId)
            ->where('school_id', $admin->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Already published
        if ($exam->is_result_published) {
            return response()->json([
                'message' => 'Results already published'
            ], 422);
        }

        // Exam must be published first
        if ($exam->status !== 'published') {
            return response()->json([
                'message' => 'Exam must be published before publishing results'
            ], 422);
        }

        // Ensure marks exist
        $marksExist = Mark::where('exam_id', $exam->id)->exists();

        if (! $marksExist) {
            return response()->json([
                'message' => 'Cannot publish results. Marks not entered yet.'
            ], 422);
        }

        // 🔒 LOCK RESULTS
        $exam->update([
            'is_result_published' => true
        ]);

        return response()->json([
            'message' => 'Results published successfully. Marks are now locked.'
        ]);
    }
}
