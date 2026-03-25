<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassSectionController extends Controller
{
    /**
     * GET /api/class-sections
     *
     * Returns:
     * Class + Section combinations
     * scoped to logged-in user's school
     */
    public function index(Request $request)
    {
        // =====================================
        // 1️⃣ Logged-in user (Sanctum)
        // =====================================
        $user = $request->user();

        if (! $user || ! $user->school_id) {
            return response()->json([
                'message' => 'Unauthorized or school not assigned'
            ], 401);
        }

        $schoolId = $user->school_id;

        // =====================================
        // 2️⃣ Fetch Classes + Sections
        // =====================================
        $classSections = DB::table('sections')
            ->join('classes', 'classes.id', '=', 'sections.class_id')
            ->where('classes.school_id', $schoolId)
            ->select(
                'classes.id as class_id',
                'classes.name as class_name',
                'sections.id as section_id',
                'sections.name as section_name'
            )
            ->orderBy('classes.name')
            ->orderBy('sections.name')
            ->get();

        // =====================================
        // 3️⃣ Return Response
        // =====================================
        return response()->json($classSections);
    }
}
