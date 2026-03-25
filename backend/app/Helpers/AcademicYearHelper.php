<?php

use App\Models\AcademicYear;
use Carbon\Carbon;

if (! function_exists('currentAcademicYearId')) {

    /**
     * Get current active academic year ID
     *
     * @param int|null $schoolId
     * @return int
     * @throws Exception
     */
    function currentAcademicYearId(?int $schoolId = null)
    {
        // 1️⃣ Determine school ID safely
        if (! $schoolId) {
            if (! auth()->check()) {
                throw new Exception('School ID not provided and user not authenticated');
            }

            $schoolId = auth()->user()->school_id;
        }

        // 2️⃣ Keep EXISTING date + status logic (UNCHANGED)
        $academicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->first();

        if (! $academicYear) {
            throw new Exception('No active academic year found for this school');
        }

        return $academicYear->id;
    }
}
