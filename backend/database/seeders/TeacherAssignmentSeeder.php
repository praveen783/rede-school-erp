<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;

class TeacherAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        if (! $academicYear) {
            $this->command->warn('No active academic year found.');
            return;
        }

        $teachers = Teacher::with('subjects')
            ->where('is_active', true)
            ->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No active teachers found.');
            return;
        }

        foreach ($teachers as $teacher) {

            // Skip teachers without subjects
            if ($teacher->subjects->isEmpty()) {
                $this->command->warn("Teacher {$teacher->name} has no subjects. Skipping.");
                continue;
            }

            // Get classes & sections for this school
            $classes = SchoolClass::where('school_id', $teacher->school_id)->get();

            if ($classes->isEmpty()) {
                $this->command->warn("No classes found for school {$teacher->school_id}");
                continue;
            }

            $classIndex = 0;

            foreach ($teacher->subjects as $subject) {

                $class = $classes[$classIndex % $classes->count()];

                $section = Section::where('class_id', $class->id)->first();

                if (! $section) {
                    $this->command->warn("No section found for class {$class->id}");
                    continue;
                }

                // Prevent duplicate assignment
                $exists = TeacherAssignment::where([
                    'teacher_id' => $teacher->id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $class->id,
                    'section_id' => $section->id,
                    'subject_id' => $subject->id,
                ])->exists();

                if ($exists) {
                    continue;
                }

                TeacherAssignment::create([
                    'school_id' => $teacher->school_id,
                    'academic_year_id' => $academicYear->id,
                    'teacher_id' => $teacher->id,
                    'class_id' => $class->id,
                    'section_id' => $section->id,
                    'subject_id' => $subject->id,
                    'is_active' => true,
                ]);

                $classIndex++;
            }
        }

        $this->command->info('Teacher assignments created successfully.');
    }
}
