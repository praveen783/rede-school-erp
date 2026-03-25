<?php

namespace App\Services;

use App\Models\TeacherAssignment;

class TeacherPermissionService
{
        public static function isAssigned(
            int $teacherId,
            int $academicYearId,
            int $classId,
            int $sectionId,
            int $subjectId
    ): bool {
        return TeacherAssignment::where([
            'teacher_id' => $teacherId,
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'subject_id' => $subjectId,
            'is_active' => true,
        ])->exists();
    }
    public static function isAssignedToClass(
        int $teacherId,
        int $academicYearId,
        int $classId,
        int $sectionId
    ): bool {
        return TeacherAssignment::where([
            'teacher_id' => $teacherId,
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'is_active' => true,
        ])->exists();
    }

}
