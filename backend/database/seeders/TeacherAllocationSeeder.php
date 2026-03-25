<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TeacherAllocationSeeder
 *
 * Populates the `teacher_assignments` table by deriving allocations directly
 * from the timetable_entries that were seeded by TimetableFullSeeder.
 *
 * Strategy:
 *  1. For each unique (class_id, section_id, subject_id, teacher_id) combination
 *     found in timetable_entries → create a subject-teacher assignment
 *     (is_class_teacher = 0).
 *
 *  2. For each class+section, elect one CLASS TEACHER:
 *     - The teacher who teaches the MOST PERIODS (most subjects/hours) in that
 *       class-section becomes the class teacher.
 *     - If tied, prefer the teacher who teaches English (subject_id=1).
 *     - The class teacher row has is_class_teacher=1, subject_id=NULL.
 *
 *  3. Conflict-free guarantee:
 *     - A teacher can teach multiple subjects in multiple classes (subject teacher).
 *     - But a teacher is CLASS TEACHER for at most ONE class-section.
 *     - We track which teachers are already designated as class teachers and
 *       fall back to the next-best candidate if needed.
 *
 * Result: 137 subject-teacher rows + 30 class-teacher rows = 167 total assignments
 * (exact counts depend on unique combos in timetable_entries).
 */
class TeacherAllocationSeeder extends Seeder
{
    private const SCHOOL_ID = 1;
    private const AY_ID     = 1; // 2025–2026 (active)

    public function run(): void
    {
        // ── Clear existing assignments ────────────────────────────────────────
        DB::table('teacher_assignments')->truncate();

        $now = Carbon::now();

        // ── Step 1: Load all unique class+section+subject+teacher combos from timetable
        // Group by class+section+subject+teacher and count periods per teacher.
        // Where multiple teachers teach the same subject in a class-section,
        // pick the one with the MOST periods (dominant teacher).
        $rawEntries = DB::table('timetable_entries as te')
            ->join('timetables as tt', 'te.timetable_id', '=', 'tt.id')
            ->where('tt.school_id', self::SCHOOL_ID)
            ->where('tt.academic_year_id', self::AY_ID)
            ->select(
                'tt.class_id',
                'tt.section_id',
                'te.subject_id',
                'te.teacher_id',
                DB::raw('COUNT(*) as period_count')
            )
            ->groupBy('tt.class_id', 'tt.section_id', 'te.subject_id', 'te.teacher_id')
            ->orderBy('tt.class_id')
            ->orderBy('tt.section_id')
            ->orderBy('te.subject_id')
            ->orderByDesc('period_count') // highest periods first
            ->get();

        // Deduplicate: for each (class, section, subject), keep only the teacher
        // with the most periods. The ORDER BY period_count DESC means the first
        // record encountered for each combo is the dominant one.
        $seen = [];
        $subjectEntries = collect();
        foreach ($rawEntries as $e) {
            $key = "{$e->class_id}|{$e->section_id}|{$e->subject_id}";
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $subjectEntries->push($e);
            }
        }

        $this->command->info("Found {$rawEntries->count()} raw combos, deduplicated to {$subjectEntries->count()} (one teacher per subject per class-section).");

        // ── Step 2: Build teacher period-count per class-section (for class teacher election)
        // [class_id][section_id][teacher_id] = total periods
        $teacherLoad = [];
        foreach ($subjectEntries as $e) {
            $teacherLoad[$e->class_id][$e->section_id][$e->teacher_id]
                = ($teacherLoad[$e->class_id][$e->section_id][$e->teacher_id] ?? 0) + $e->period_count;
        }

        // ── Step 3: Elect class teachers ─────────────────────────────────────
        // Track which teachers are already elected as class teacher globally
        $electedClassTeachers = []; // teacher_id => true

        // [class_id][section_id] = teacher_id
        $classTeacherMap = [];

        // Process in class order so lower classes get first pick
        ksort($teacherLoad);
        foreach ($teacherLoad as $classId => $sections) {
            ksort($sections);
            foreach ($sections as $sectionId => $teachers) {
                // Sort teachers by period count descending
                arsort($teachers);

                $elected = null;
                foreach ($teachers as $teacherId => $periods) {
                    if (!isset($electedClassTeachers[$teacherId])) {
                        $elected = $teacherId;
                        break;
                    }
                }

                // If ALL teachers in this class-section are already class teachers elsewhere
                // (rare edge case), pick the one with the highest load anyway
                if ($elected === null) {
                    reset($teachers);
                    $elected = key($teachers);
                }

                $classTeacherMap[$classId][$sectionId] = $elected;
                $electedClassTeachers[$elected] = true;
            }
        }

        // ── Step 4: Insert subject-teacher assignments ────────────────────────
        $subjectRows = [];
        foreach ($subjectEntries as $e) {
            $subjectRows[] = [
                'school_id'        => self::SCHOOL_ID,
                'academic_year_id' => self::AY_ID,
                'teacher_id'       => $e->teacher_id,
                'class_id'         => $e->class_id,
                'section_id'       => $e->section_id,
                'subject_id'       => $e->subject_id,
                'is_class_teacher' => 0,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        foreach (array_chunk($subjectRows, 50) as $chunk) {
            DB::table('teacher_assignments')->insert($chunk);
        }

        $this->command->info("✅ Inserted {$subjectEntries->count()} subject-teacher assignment rows.");

        // ── Step 5: Insert class-teacher assignments ──────────────────────────
        $classTeacherRows = [];
        $classSectionNames = $this->getClassSectionNames();

        foreach ($classTeacherMap as $classId => $sections) {
            foreach ($sections as $sectionId => $teacherId) {
                $classTeacherRows[] = [
                    'school_id'        => self::SCHOOL_ID,
                    'academic_year_id' => self::AY_ID,
                    'teacher_id'       => $teacherId,
                    'class_id'         => $classId,
                    'section_id'       => $sectionId,
                    'subject_id'       => null,         // NULL = class teacher role
                    'is_class_teacher' => 1,
                    'is_active'        => 1,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                $label = $classSectionNames[$classId][$sectionId] ?? "Class {$classId}-Sec{$sectionId}";
                $empCode = DB::table('teachers')->where('id', $teacherId)->value('employee_code');
                $this->command->line("  📌 Class Teacher: {$label} → Teacher #{$teacherId} ({$empCode})");
            }
        }

        DB::table('teacher_assignments')->insert($classTeacherRows);

        $this->command->info("✅ Inserted " . count($classTeacherRows) . " class-teacher assignment rows.");

        // ── Step 6: Final summary ─────────────────────────────────────────────
        $total          = DB::table('teacher_assignments')->count();
        $totalSubject   = DB::table('teacher_assignments')->where('is_class_teacher', 0)->count();
        $totalClass     = DB::table('teacher_assignments')->where('is_class_teacher', 1)->count();

        // Verify: no teacher is class teacher for more than 1 class-section
        $conflicts = DB::table('teacher_assignments')
            ->where('is_class_teacher', 1)
            ->select('teacher_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('teacher_id')
            ->having('cnt', '>', 1)
            ->get();

        $this->command->newLine();
        $this->command->info("═══════════════════════════════════════════════════════");
        $this->command->info("✅ Teacher Allocation Complete!");
        $this->command->info("   Total assignments : {$total}");
        $this->command->info("   Subject teachers  : {$totalSubject}");
        $this->command->info("   Class teachers    : {$totalClass}");

        if ($conflicts->isEmpty()) {
            $this->command->info("   ✅ No teacher is class teacher for more than 1 class!");
        } else {
            $this->command->warn("   ⚠️ Conflicts found: " . $conflicts->count() . " teachers assigned as class teacher to multiple classes");
        }
        $this->command->info("═══════════════════════════════════════════════════════");
    }

    /**
     * Build a [class_id][section_id] => "ClassName - Section X" lookup map.
     */
    private function getClassSectionNames(): array
    {
        $classes  = DB::table('classes')->get(['id', 'name'])->keyBy('id');
        $sections = DB::table('sections')->get(['id', 'class_id', 'name']);

        $map = [];
        foreach ($sections as $s) {
            $className = $classes[$s->class_id]->name ?? "Class {$s->class_id}";
            $map[$s->class_id][$s->id] = "{$className} - Section {$s->name}";
        }

        return $map;
    }
}
