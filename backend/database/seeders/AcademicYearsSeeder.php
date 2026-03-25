<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\School;

class AcademicYearsSeeder extends Seeder
{
    public function run(): void
    {
        // Get all schools (school-based isolation)
        $schools = School::all();

        foreach ($schools as $school) {

            // Ensure only ONE active academic year per school
            AcademicYear::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'name' => '2025–2026',
                ],
                [
                    'start_date' => '2025-06-01',
                    'end_date'   => '2026-05-31',
                    'is_active'  => 1,
                    'status'     => 'active',
                ]
            );

            // OPTIONAL: Example of previous year (inactive)
            AcademicYear::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'name' => '2024–2025',
                ],
                [
                    'start_date' => '2024-06-01',
                    'end_date'   => '2025-05-31',
                    'is_active'  => 0,
                    'status'     => 'archived',
                ]
            );
        }
    }
}
