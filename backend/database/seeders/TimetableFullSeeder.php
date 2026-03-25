<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TimetableFullSeeder — v2 (Conflict-Free, Full Coverage)
 *
 * Seeds:
 *  - 9 periods (8 teaching + 1 lunch break) for school_id = 1
 *  - 1 timetable per class-section (15 classes × 2 sections = 30 timetables)
 *  - Timetable entries (Mon–Sat) ensuring NO teacher is double-booked in the
 *    same period slot across different classes.
 *
 * Subject IDs:
 *   1=English, 2=Hindi, 3=Sanskrit, 4=Math, 5=Env.Sci, 6=Science,
 *   7=Social Sci, 8=Physics, 9=Chemistry, 10=Biology, 11=History,
 *   12=Geography, 13=Economics, 14=Pol.Science, 15=Computer Sci,
 *   16=GK, 17=Moral Sci, 18=Drawing & Craft, 19=PE
 *
 * Teacher → Subjects:
 *   6  → Math(4), Science(6)
 *   8  → GK(16), Moral Sci(17), Drawing(18), PE(19)
 *   9  → GK(16), Moral Sci(17), Drawing(18)
 *   10 → GK(16), Moral Sci(17), Drawing(18)
 *   11 → English(1), Env.Sci(5)
 *   12 → Math(4), CS(15)
 *   13 → Hindi(2), Sanskrit(3)
 *   14 → Env.Sci(5), Science(6), Biology(10)
 *   15 → Drawing(18), PE(19)
 *   16 → Math(4), CS(15)
 *   17 → Science(6), Biology(10)
 *   18 → Social Sci(7), History(11), Geography(12)
 *   19 → English(1), Sanskrit(3)
 *   20 → Hindi(2), Moral Sci(17)
 *   21 → Drawing(18), PE(19)
 *   22 → Math(4), Physics(8)
 *   23 → Chemistry(9), Biology(10)
 *   24 → English(1), CS(15)
 *   25 → Hindi(2), Sanskrit(3)
 *   26 → Social Sci(7), History(11), Geo(12), Economics(13), Pol.Sci(14)
 *   27 → GK(16), PE(19)
 */
class TimetableFullSeeder extends Seeder
{
    private const SCHOOL_ID = 1;
    private const AY_ID     = 1;

    private const ENG = 1;
    private const HIN = 2;
    private const SAN = 3;
    private const MAT = 4;
    private const ENV = 5;
    private const SCI = 6;
    private const SST = 7;
    private const PHY = 8;
    private const CHE = 9;
    private const BIO = 10;
    private const HIS = 11;
    private const GEO = 12;
    private const ECO = 13;
    private const CIV = 14;
    private const CS  = 15;
    private const GK  = 16;
    private const MOR = 17;
    private const DRW = 18;
    private const PE  = 19;

    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('timetable_entries')->truncate();
        DB::table('timetables')->truncate();
        DB::table('periods')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now            = Carbon::now();
        $periodIds      = $this->createPeriods($now);
        $teachingOrders = [1, 2, 3, 4, 6, 7, 8, 9]; // skip order 5 = Lunch Break

        // Pre-load subject → qualified teachers from pivot table
        $subjectTeachersMap = [];
        foreach (DB::table('teacher_subjects')->get(['subject_id', 'teacher_id']) as $r) {
            $subjectTeachersMap[$r->subject_id][] = $r->teacher_id;
        }

        // ── Step 1: Create all 30 timetable rows & load their per-section data ──
        $sections = []; // array of section descriptors
        for ($classId = 1; $classId <= 15; $classId++) {
            foreach (['A', 'B'] as $sec) {
                $sectionId   = ($sec === 'A') ? ($classId * 2) - 1 : ($classId * 2);
                $timetableId = DB::table('timetables')->insertGetId([
                    'school_id'        => self::SCHOOL_ID,
                    'academic_year_id' => self::AY_ID,
                    'class_id'         => $classId,
                    'section_id'       => $sectionId,
                    'is_active'        => 1,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);

                $subjectPool      = $this->getSubjectRotation($classId);
                $preferredTeacher = $this->getSubjectTeacherMap($classId, $sec);

                $sections[] = [
                    'classId'          => $classId,
                    'section'          => $sec,
                    'timetableId'      => $timetableId,
                    'subjectPool'      => $subjectPool,
                    'preferredTeacher' => $preferredTeacher,
                ];
            }
        }

        // ── Step 2: Fill entries using round-robin across all sections ───────
        // We process sections in a rotating order so that no section is
        // starved. Within each (day, period_order) slot we shuffle sections
        // so that the ORDER in which classes are tried changes every slot.
        // This distributes teacher capacity evenly across all 30 sections.
        //
        // Global booking: [day][period_order][teacher_id] = true
        $globalBooked    = [];
        $entriesToInsert = [];
        $entryCount      = 0;
        $skippedSlots    = 0;

        $sectionCount = count($sections);

        foreach (self::DAYS as $dayIdx => $day) {
            foreach ($teachingOrders as $slotIdx => $order) {
                // Rotate which section gets priority — different each slot
                $rotateBy = ($dayIdx * 8 + $slotIdx) % $sectionCount;
                $order2 = array_merge(
                    array_slice($sections, $rotateBy),
                    array_slice($sections, 0, $rotateBy)
                );

                foreach ($order2 as $sIdx => $s) {
                    $poolSize = count($s['subjectPool']);

                    // Unique pool-start per section per slot to vary subject choices
                    $classOff  = ($s['classId'] - 1) * 2 + ($s['section'] === 'B' ? 1 : 0);
                    $poolStart = (($classOff * 7 + $dayIdx * 8 + $slotIdx) * 3) % $poolSize;

                    // Try subjects from pool starting at poolStart until one fits
                    $placed = false;
                    for ($attempt = 0; $attempt < $poolSize; $attempt++) {
                        $subjId = $s['subjectPool'][($poolStart + $attempt) % $poolSize];
                        $tid    = $s['preferredTeacher'][$subjId] ?? null;

                        if ($tid === null) {
                            $tid = $this->pickFreeTeacher(
                                $subjId, $day, $order, $globalBooked, $subjectTeachersMap
                            );
                        } elseif (isset($globalBooked[$day][$order][$tid])) {
                            $alt = $this->pickFreeTeacher(
                                $subjId, $day, $order, $globalBooked, $subjectTeachersMap, $tid
                            );
                            $tid = $alt;
                        }

                        if ($tid !== null && !isset($globalBooked[$day][$order][$tid])) {
                            $globalBooked[$day][$order][$tid] = true;
                            $entriesToInsert[] = [
                                'timetable_id' => $s['timetableId'],
                                'day_of_week'  => $day,
                                'period_id'    => $periodIds[$order],
                                'subject_id'   => $subjId,
                                'teacher_id'   => $tid,
                                'created_at'   => $now,
                                'updated_at'   => $now,
                            ];
                            $entryCount++;
                            $placed = true;
                            break;
                        }
                    }

                    if (!$placed) {
                        $skippedSlots++;
                    }
                }
            }
        }

        // ── Step 3: Bulk insert all entries ──────────────────────────────────
        foreach (array_chunk($entriesToInsert, 200) as $chunk) {
            DB::table('timetable_entries')->insert($chunk);
        }

        $this->command->info('✅ Created ' . count($sections) . " timetables with {$entryCount} entries.");
        if ($skippedSlots > 0) {
            $this->command->warn("⚠️  {$skippedSlots} slots could not be filled (teacher capacity limit).");
        } else {
            $this->command->info('🎯 All slots filled — conflict-free timetable seeded!');
        }
    }

    private function pickFreeTeacher(
        int $subjectId,
        string $day,
        int $order,
        array &$globalBooked,
        array $subjectTeachersMap,
        ?int $excludeTeacherId = null
    ): ?int {
        $candidates = $subjectTeachersMap[$subjectId] ?? [];
        foreach ($candidates as $tid) {
            if ($tid === $excludeTeacherId) {
                continue;
            }
            if (!isset($globalBooked[$day][$order][$tid])) {
                return $tid;
            }
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Period creation
    // ─────────────────────────────────────────────────────────────────────────
    private function createPeriods(Carbon $now): array
    {
        $periodsData = [
            // order => [name, start, end, is_break]
            1 => ['Period 1',     '08:00:00', '08:45:00', 0],
            2 => ['Period 2',     '08:45:00', '09:30:00', 0],
            3 => ['Period 3',     '09:30:00', '10:15:00', 0],
            4 => ['Period 4',     '10:15:00', '11:00:00', 0],
            5 => ['Lunch Break',  '11:00:00', '11:30:00', 1], // BREAK
            6 => ['Period 5',     '11:30:00', '12:15:00', 0],
            7 => ['Period 6',     '12:15:00', '13:00:00', 0],
            8 => ['Period 7',     '13:00:00', '13:45:00', 0],
            9 => ['Period 8',     '13:45:00', '14:30:00', 0],
        ];

        $ids = [];
        foreach ($periodsData as $order => $p) {
            $ids[$order] = DB::table('periods')->insertGetId([
                'school_id'  => self::SCHOOL_ID,
                'name'       => $p[0],
                'start_time' => $p[1],
                'end_time'   => $p[2],
                'order'      => $order,
                'is_break'   => $p[3],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $ids; // [order => id]
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Weekly subject pool per class group
    // Returns a flat array of subject_ids used repeatedly across the week.
    // The array is designed so that weekly distribution is realistic:
    //   - Core subjects appear more frequently
    //   - Activity subjects appear 1–2x per week
    // For 6 days × 8 periods = 48 slots per week
    // ─────────────────────────────────────────────────────────────────────────
    private function getSubjectRotation(int $classId): array
    {
        // Nursery (1), LKG (2), UKG (3)
        if ($classId <= 3) {
            return [
                self::ENG, self::ENG, self::HIN, self::HIN,
                self::MAT, self::MAT, self::GK,  self::DRW,
                self::ENG, self::HIN, self::MAT, self::GK,
                self::DRW, self::MOR, self::PE,  self::ENG,
                self::HIN, self::MAT, self::GK,  self::DRW,
                self::MOR, self::PE,  self::ENG, self::HIN,
                self::MAT, self::GK,  self::DRW, self::MOR,
                self::PE,  self::ENG, self::HIN, self::MAT,
                self::GK,  self::DRW, self::MOR, self::PE,
                self::ENG, self::HIN, self::MAT, self::GK,
                self::DRW, self::MOR, self::PE,  self::ENG,
                self::HIN, self::MAT, self::GK,  self::DRW,
            ];
        }

        // Class 1–2 (ids 4–5)
        if ($classId <= 5) {
            return [
                self::ENG, self::HIN, self::MAT, self::ENV,
                self::GK,  self::DRW, self::MOR, self::PE,
                self::ENG, self::HIN, self::MAT, self::ENV,
                self::GK,  self::DRW, self::ENG, self::HIN,
                self::MAT, self::ENV, self::GK,  self::MOR,
                self::PE,  self::ENG, self::HIN, self::MAT,
                self::ENV, self::GK,  self::DRW, self::MOR,
                self::PE,  self::ENG, self::HIN, self::MAT,
                self::ENV, self::GK,  self::DRW, self::ENG,
                self::HIN, self::MAT, self::ENV, self::GK,
                self::MOR, self::PE,  self::ENG, self::HIN,
                self::MAT, self::ENV, self::DRW, self::GK,
            ];
        }

        // Class 3–5 (ids 6–8)
        if ($classId <= 8) {
            return [
                self::ENG, self::HIN, self::MAT, self::ENV,
                self::SCI, self::SST, self::DRW, self::MOR,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::SST, self::ENV, self::PE,  self::GK,
                self::ENG, self::HIN, self::MAT, self::ENV,
                self::SCI, self::SST, self::DRW, self::MOR,
                self::ENG, self::MAT, self::HIN, self::SCI,
                self::SST, self::ENV, self::PE,  self::GK,
                self::ENG, self::HIN, self::MAT, self::ENV,
                self::SCI, self::SST, self::DRW, self::MOR,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::SST, self::PE,  self::GK,  self::DRW,
            ];
        }

        // Class 6–8 (ids 9–11) — Sanskrit introduced
        if ($classId <= 11) {
            return [
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::SST, self::SAN, self::DRW, self::PE,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::SST, self::SAN, self::GK,  self::MOR,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::SST, self::SAN, self::DRW, self::PE,
                self::ENG, self::MAT, self::HIN, self::SCI,
                self::SST, self::SAN, self::GK,  self::MOR,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::SST, self::SAN, self::DRW, self::PE,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::SST, self::SAN, self::GK,  self::MOR,
            ];
        }

        // Class 9–10 (ids 12–13) — Sciences split, History/Geo, CS optional
        if ($classId <= 13) {
            return [
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::HIS, self::GEO, self::SAN, self::PE,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::HIS, self::GEO, self::DRW, self::MOR,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::HIS, self::GEO, self::SAN, self::PE,
                self::ENG, self::MAT, self::HIN, self::SCI,
                self::HIS, self::GEO, self::CS,  self::MOR,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::HIS, self::GEO, self::SAN, self::PE,
                self::ENG, self::HIN, self::MAT, self::SCI,
                self::HIS, self::GEO, self::CS,  self::DRW,
            ];
        }

        // Class 11–12 (ids 14–15) — PHY, CHE, BIO/ECO/CIV, CS
        return [
            self::ENG, self::HIN, self::MAT, self::PHY,
            self::CHE, self::BIO, self::ECO, self::CS,
            self::ENG, self::HIN, self::MAT, self::PHY,
            self::CHE, self::BIO, self::CIV, self::PE,
            self::ENG, self::HIN, self::MAT, self::PHY,
            self::CHE, self::BIO, self::ECO, self::CS,
            self::ENG, self::MAT, self::HIN, self::PHY,
            self::CHE, self::BIO, self::CIV, self::PE,
            self::ENG, self::HIN, self::MAT, self::PHY,
            self::CHE, self::BIO, self::ECO, self::CS,
            self::ENG, self::HIN, self::MAT, self::PHY,
            self::CHE, self::BIO, self::CIV, self::DRW,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Map subject_id → teacher_id for a specific class+section
    // Section A and B get different teachers for the same subject where possible,
    // to distribute workload. Both sections of the same class run in parallel,
    // which is why teacher conflicts can arise — resolved in run().
    // ─────────────────────────────────────────────────────────────────────────
    private function getSubjectTeacherMap(int $classId, string $section): array
    {
        // Full teacher→subject map (teacher_id → [subject_ids])
        // Used to assign the right teacher per subject per class group

        // ── Nursery / LKG / UKG ──────────────────────────────────────────
        if ($classId <= 3) {
            if ($section === 'A') {
                return [
                    self::ENG => 11,  // T11: English, Env.Sci
                    self::HIN => 20,  // T20: Hindi, Moral Sci
                    self::MAT => 12,  // T12: Math, CS
                    self::GK  => 8,   // T8:  GK, Moral, Drawing, PE
                    self::DRW => 9,   // T9:  GK, Moral, Drawing
                    self::MOR => 8,   // T8
                    self::PE  => 15,  // T15: Drawing, PE
                ];
            } else {
                return [
                    self::ENG => 19,  // T19: English, Sanskrit
                    self::HIN => 13,  // T13: Hindi, Sanskrit
                    self::MAT => 6,   // T6:  Math, Science
                    self::GK  => 9,   // T9:  GK, Moral, Drawing
                    self::DRW => 10,  // T10: GK, Moral, Drawing
                    self::MOR => 20,  // T20: Hindi, Moral Sci
                    self::PE  => 27,  // T27: GK, PE
                ];
            }
        }

        // ── Class 1–2 ─────────────────────────────────────────────────────
        if ($classId <= 5) {
            if ($section === 'A') {
                return [
                    self::ENG => 11,
                    self::HIN => 20,
                    self::MAT => 12,
                    self::ENV => 11,  // T11: English, Env.Sci
                    self::GK  => 8,
                    self::DRW => 9,
                    self::MOR => 8,
                    self::PE  => 15,
                ];
            } else {
                return [
                    self::ENG => 24,  // T24: English, CS
                    self::HIN => 25,  // T25: Hindi, Sanskrit
                    self::MAT => 16,  // T16: Math, CS
                    self::ENV => 14,  // T14: Env.Sci, Science, Bio
                    self::GK  => 10,
                    self::DRW => 10,
                    self::MOR => 20,
                    self::PE  => 21,  // T21: Drawing, PE
                ];
            }
        }

        // ── Class 3–5 ─────────────────────────────────────────────────────
        if ($classId <= 8) {
            if ($section === 'A') {
                return [
                    self::ENG => 11,
                    self::HIN => 13,
                    self::MAT => 6,
                    self::ENV => 14,
                    self::SCI => 14,
                    self::SST => 18,  // T18: Social Sci, History, Geo
                    self::DRW => 15,
                    self::MOR => 20,
                    self::PE  => 27,
                    self::GK  => 8,
                ];
            } else {
                return [
                    self::ENG => 19,
                    self::HIN => 20,
                    self::MAT => 12,
                    self::ENV => 11,
                    self::SCI => 17,  // T17: Science, Bio
                    self::SST => 26,  // T26: Social Sci, History, Geo, Eco, Civ
                    self::DRW => 21,
                    self::MOR => 9,
                    self::PE  => 15,
                    self::GK  => 27,
                ];
            }
        }

        // ── Class 6–8 ─────────────────────────────────────────────────────
        if ($classId <= 11) {
            if ($section === 'A') {
                return [
                    self::ENG => 24,
                    self::HIN => 25,
                    self::MAT => 16,
                    self::SCI => 6,
                    self::SST => 18,
                    self::SAN => 19,  // T19: English, Sanskrit
                    self::DRW => 9,
                    self::MOR => 8,
                    self::PE  => 21,
                    self::GK  => 10,
                ];
            } else {
                return [
                    self::ENG => 11,
                    self::HIN => 13,
                    self::MAT => 22,  // T22: Math, Physics
                    self::SCI => 14,
                    self::SST => 26,
                    self::SAN => 25,
                    self::DRW => 15,
                    self::MOR => 20,
                    self::PE  => 27,
                    self::GK  => 27,
                ];
            }
        }

        // ── Class 9–10 ────────────────────────────────────────────────────
        if ($classId <= 13) {
            if ($section === 'A') {
                return [
                    self::ENG => 19,
                    self::HIN => 20,
                    self::MAT => 22,
                    self::SCI => 17,
                    self::HIS => 18,
                    self::GEO => 18,
                    self::SAN => 13,
                    self::CS  => 12,
                    self::DRW => 9,
                    self::MOR => 8,
                    self::PE  => 15,
                ];
            } else {
                return [
                    self::ENG => 24,
                    self::HIN => 25,
                    self::MAT => 16,
                    self::SCI => 6,
                    self::HIS => 26,
                    self::GEO => 26,
                    self::SAN => 19,
                    self::CS  => 16,
                    self::DRW => 21,
                    self::MOR => 20,
                    self::PE  => 21,
                ];
            }
        }

        // ── Class 11–12 ───────────────────────────────────────────────────
        if ($section === 'A') {
            return [
                self::ENG => 24,
                self::HIN => 25,
                self::MAT => 22,
                self::PHY => 22,
                self::CHE => 23,  // T23: Chemistry, Bio
                self::BIO => 14,
                self::ECO => 26,
                self::CIV => 26,
                self::CS  => 12,
                self::PE  => 21,
                self::DRW => 15,
            ];
        } else {
            return [
                self::ENG => 19,
                self::HIN => 20,
                self::MAT => 16,
                self::PHY => 22,
                self::CHE => 23,
                self::BIO => 17,
                self::ECO => 26,
                self::CIV => 26,
                self::CS  => 24,
                self::PE  => 27,
                self::DRW => 9,
            ];
        }
    }

}
