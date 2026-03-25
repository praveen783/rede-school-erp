<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Models
use App\Models\Student;
use App\Models\Teacher;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard summary for Admin / School Admin
     */
    public function summary(Request $request)
    {
        $user = $request->user();

        // Safety check (extra layer)
        if (!$user || !$user->school_id) {
            return response()->json([
                'message' => 'Invalid user or school context'
            ], 403);
        }

        $schoolId = $user->school_id;

        return response()->json([
            'total_students' => Student::where('school_id', $schoolId)->count(),

            'active_students' => Student::where('school_id', $schoolId)
                ->where('is_active', true)
                ->count(),

            'inactive_students' => Student::where('school_id', $schoolId)
                ->where('is_active', false)
                ->count(),

            'total_teachers' => Teacher::where('school_id', $schoolId)->count(),
        ]);
    }
}
