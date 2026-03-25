<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function studentResult(Request $request)
    {
        $user = auth()->user();

        // Optional filter
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        // Resolve logged-in student
        $student = Student::where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        $exam = Exam::with('subjects')
            ->where('id', $request->exam_id)
            ->where('school_id', $user->school_id)
            ->where('academic_year_id', $user->academic_year_id)
            ->firstOrFail();

        // 🔒 Result publish check
        if (! $exam->is_result_published) {
            return response()->json([
                'message' => 'Results not published yet'
            ], 403);
        }

        $marks = Mark::with('subject')
            ->where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->where('school_id', $user->school_id)
            ->where('academic_year_id', $user->academic_year_id)
            ->get();

        if ($marks->isEmpty()) {
            return response()->json([
                'message' => 'No marks found'
            ], 404);
        }

        $subjectsResult = [];
        $totalObtained = 0;
        $totalMax = 0;
        $overallPass = true;

        foreach ($marks as $mark) {

            $pivot = $exam->subjects
                ->where('id', $mark->subject_id)
                ->first()
                ->pivot;

            $maxMarks = $pivot->max_marks;
            $passMarks = $pivot->pass_marks;

            $obtained = $mark->is_absent ? 0 : $mark->marks_obtained;
            $status = $mark->is_absent
                ? 'ABSENT'
                : ($obtained >= $passMarks ? 'PASS' : 'FAIL');

            if ($status === 'FAIL') {
                $overallPass = false;
            }

            $subjectsResult[] = [
                'subject' => $mark->subject->name,
                'marks_obtained' => $mark->is_absent ? null : $obtained,
                'max_marks' => $maxMarks,
                'pass_marks' => $passMarks,
                'status' => $status,
            ];

            $totalObtained += $obtained;
            $totalMax += $maxMarks;
        }

        $percentage = $totalMax > 0
            ? round(($totalObtained / $totalMax) * 100, 2)
            : 0;

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
            ],
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
            ],
            'subjects' => $subjectsResult,
            'summary' => [
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage,
                'result' => $overallPass ? 'PASS' : 'FAIL',
            ]
        ]);
    }

    public function classResult(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $exam = Exam::with('subjects')->findOrFail($request->exam_id);

        // 🔒 Result publish check
        if (!$exam->is_result_published) {
            return response()->json([
                'message' => 'Results not published yet'
            ], 403);
        }

        // Get all students in class & section
        $students = Student::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('is_active', true)
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'message' => 'No students found for this class and section'
            ], 404);
        }

        $results = [];
        $passCount = 0;
        $failCount = 0;

        foreach ($students as $student) {

            $marks = Mark::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->get();

            if ($marks->isEmpty()) {
                continue;
            }

            $totalObtained = 0;
            $totalMax = 0;
            $overallPass = true;

            foreach ($marks as $mark) {
                $pivot = $exam->subjects
                    ->where('id', $mark->subject_id)
                    ->first()
                    ->pivot;

                $maxMarks = $pivot->max_marks;
                $passMarks = $pivot->pass_marks;

                $obtained = $mark->is_absent ? 0 : $mark->marks_obtained;

                if ($obtained < $passMarks) {
                    $overallPass = false;
                }

                $totalObtained += $obtained;
                $totalMax += $maxMarks;
            }

            $percentage = $totalMax > 0
                ? round(($totalObtained / $totalMax) * 100, 2)
                : 0;

            if ($overallPass) {
                $passCount++;
            } else {
                $failCount++;
            }

            $results[] = [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage,
                'result' => $overallPass ? 'PASS' : 'FAIL',
            ];
        }

        // 🔢 Rank calculation
        usort($results, fn ($a, $b) => $b['percentage'] <=> $a['percentage']);

        $rank = 1;
        foreach ($results as &$row) {
            $row['rank'] = $rank++;
        }

        $totalStudents = count($results);
        $passPercentage = $totalStudents > 0
            ? round(($passCount / $totalStudents) * 100, 2)
            : 0;

        return response()->json([
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
            ],
            'class_summary' => [
                'total_students' => $totalStudents,
                'passed' => $passCount,
                'failed' => $failCount,
                'pass_percentage' => $passPercentage,
            ],
            'results' => $results
        ]);
    }

}
