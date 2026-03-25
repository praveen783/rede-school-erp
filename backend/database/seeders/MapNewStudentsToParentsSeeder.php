<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\ParentProfile; 
use Illuminate\Support\Facades\DB;

class MapNewStudentsToParentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $schoolId = 1;

            // 1️⃣ Get parents (existing only)
            $parents = ParentProfile::where('school_id', $schoolId)->orderBy('id')->get();

            if ($parents->isEmpty()) {
                $this->command->warn('No parents found. Skipping mapping.');
                return;
            }

            // 2️⃣ Get ONLY newly added students
            $students = Student::where('school_id', $schoolId)
                ->whereNull('parent_id') // 🔑 THIS protects existing mappings
                ->orderBy('id')
                ->get();

            $parentIndex = 0;
            $currentParent = null;

            foreach ($students as $index => $student) {

                // Assign 2 students per parent
                if ($index % 2 === 0) {
                    $currentParent = $parents[$parentIndex % $parents->count()];
                    $parentIndex++;
                }

                // 3️⃣ Update students table (PRIMARY)
                $student->update([
                    'parent_id'   => $currentParent->id,
                    'parent_name' => $currentParent->name,
                ]);

                // 4️⃣ Mirror into pivot table (SECONDARY)
                DB::table('parent_student')->insert([
                    'parent_id'  => $currentParent->id,
                    'student_id' => $student->id,
                    'school_id'  => $schoolId,
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ]);
            }
        });
    }
}
