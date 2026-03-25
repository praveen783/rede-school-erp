<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamScheduleController extends Controller
{
    /**
     * STORE – Create schedules for an exam + class + section
     */
   public function store(Request $request)
    {
        $request->validate([
            'exam_id'   => 'required|exists:exams,id',
            'schedules' => 'required|array|min:1',

            'schedules.*.subject_id' => 'required|exists:subjects,id',
            'schedules.*.exam_date'  => 'required|date',
            'schedules.*.start_time' => 'required',
            'schedules.*.end_time'   => 'required',
        ]);

        $schoolId       = auth()->user()->school_id;
        $academicYearId = currentAcademicYearId();

        // Validate subject belongs to exam
        $validSubjects = DB::table('exam_subjects')
            ->where('exam_id', $request->exam_id)
            ->pluck('subject_id')
            ->toArray();

        DB::transaction(function () use (
            $request,
            $schoolId,
            $academicYearId,
            $validSubjects
        ) {
            foreach ($request->schedules as $row) {

                if (!in_array($row['subject_id'], $validSubjects)) {
                    abort(422, 'Invalid subject for this exam.');
                }

                ExamSchedule::updateOrCreate(
                    [
                        'exam_id'    => $request->exam_id,
                        'subject_id' => $row['subject_id'],
                    ],
                    [
                        'school_id'        => $schoolId,
                        'academic_year_id' => $academicYearId,
                        'exam_date'        => $row['exam_date'],
                        'start_time'       => $row['start_time'],
                        'end_time'         => $row['end_time'],
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Exam schedule saved successfully.'
        ]);
    }
    /**
     * UPDATE – Replace entire schedule for exam + class + section
     */
    public function update(Request $request, $examId, $classId, $sectionId)
    {
        $request->validate([
            'schedules' => 'required|array|min:1',
            'schedules.*.subject_id' => 'required|exists:subjects,id',
            'schedules.*.exam_date'  => 'required|date',
            'schedules.*.start_time' => 'required',
            'schedules.*.end_time'   => 'required',
        ]);

        DB::transaction(function () use ($examId, $classId, $sectionId, $request) {

            ExamSchedule::where([
                'exam_id'    => $examId,
                'class_id'   => $classId,
                'section_id' => $sectionId,
            ])->delete();

            foreach ($request->schedules as $row) {
                ExamSchedule::create([
                    'school_id'        => auth()->user()->school_id,
                    'academic_year_id'=> currentAcademicYearId(),
                    'exam_id'          => $examId,
                    'class_id'         => $classId,
                    'section_id'       => $sectionId,
                    'subject_id'       => $row['subject_id'],
                    'exam_date'        => $row['exam_date'],
                    'start_time'       => $row['start_time'],
                    'end_time'         => $row['end_time'],
                ]);
            }
        });

        return response()->json([
            'message' => 'Exam schedule updated successfully.'
        ]);
    }

    /**
     * DELETE – Remove entire exam schedule
     */
    public function destroy($examId, $classId, $sectionId)
    {
        ExamSchedule::where([
            'exam_id'    => $examId,
            'class_id'   => $classId,
            'section_id' => $sectionId,
        ])->delete();

        return response()->json([
            'message' => 'Exam schedule deleted successfully.'
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|integer|exists:exams,id',
        ]);

        $examId = $request->exam_id;

        // 1️⃣ Get exam (to know its class & section)
        $exam = Exam::findOrFail($examId);

        // 2️⃣ Get subjects mapped to this exam
        $subjects = \DB::table('exam_subjects')
            ->join('subjects', 'subjects.id', '=', 'exam_subjects.subject_id')
            ->where('exam_subjects.exam_id', $examId)
            ->select(
                'subjects.id as subject_id',
                'subjects.name as subject_name'
            )
            ->get();

        // 3️⃣ Get existing schedules for this exam
        $schedules = \DB::table('exam_schedules')
            ->where('exam_id', $examId)
            ->get()
            ->keyBy('subject_id');

        // 4️⃣ Merge subjects + schedule
        $data = $subjects->map(function ($subject) use ($schedules) {

            $schedule = $schedules[$subject->subject_id] ?? null;

            return [
                'subject_id' => $subject->subject_id,
                'subject'    => $subject->subject_name,
                'exam_date'  => $schedule->exam_date ?? null,
                'start_time' => $schedule->start_time ?? null,
                'end_time'   => $schedule->end_time ?? null,
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }

}
