<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\Subject;

class TeacherSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = 1;

        // Fetch subjects
        $math = Subject::where('name', 'Mathematics')->first();
        $physics = Subject::where('name', 'Physics')->first();
        $cs = Subject::where('name', 'Computer Science')->first();

        // Fetch teachers
        $anil = Teacher::where('employee_code', 'EMP101')->first();
        $sunita = Teacher::where('employee_code', 'EMP102')->first();
        $neha = Teacher::where('employee_code', 'EMP104')->first();

        // Safety checks (important)
        if (!$math || !$physics || !$cs) {
            return;
        }

        // Anil → Mathematics + Physics
        $anil?->subjects()->syncWithoutDetaching([
            $math->id => ['school_id' => $schoolId],
            $physics->id => ['school_id' => $schoolId],
        ]);

        // Sunita → Mathematics
        $sunita?->subjects()->syncWithoutDetaching([
            $math->id => ['school_id' => $schoolId],
        ]);

        // Neha → Computer Science
        $neha?->subjects()->syncWithoutDetaching([
            $cs->id => ['school_id' => $schoolId],
        ]);
  
        // Ravi (EMP103) intentionally left without subjects (edge case)
    }
}
