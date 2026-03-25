<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Core
            SchoolsSeeder::class,
            UsersSeeder::class,

            // Academic Structure
            AcademicYearsSeeder::class,
            ClassesSeeder::class,
            SectionsSeeder::class,

            // Staff
            SubjectsSeeder::class,
            TeachersSeeder::class,
            TeacherSubjectSeeder::class,

            // Parents & Students
            ParentsSeeder::class,
            StudentsSeeder::class,

            //Exmas
            ExamSeeder::class,
            ClassTeacherAssignmentSeeder::class,

            // 🔥 BULK TEST DATA
            BulkStudentsSeeder::class,
            ClassSubjectBulkSeeder::class,
            ClassWiseExamSeeder::class,

            BoardSeeder::class,
            AttendanceSeeder::class,

        ]);
    }
}
