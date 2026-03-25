<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassTeacherAssignment;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;

class ClassTeacherAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch required parent records
        $school = School::first();
        $academicYear = AcademicYear::where('is_active', 1)->first();

        // Teacher must be a USER with role = teacher
        $teacherUser = User::where('role', 'teacher')->first();

        $class = SchoolClass::first();
        $section = Section::first();

        // Safety check (VERY IMPORTANT)
        if (! $school || ! $academicYear || ! $teacherUser || ! $class || ! $section) {
            $this->command->warn(
                'Skipping ClassTeacherAssignmentSeeder: missing required data'
            );
            return;
        }

        // Create assignment
        ClassTeacherAssignment::firstOrCreate(
            [
                'school_id'        => $school->id,
                'teacher_id'       => $teacherUser->id,
                'class_id'         => $class->id,
                'section_id'       => $section->id,
                'academic_year_id' => $academicYear->id,
            ]
        );
    }
}
