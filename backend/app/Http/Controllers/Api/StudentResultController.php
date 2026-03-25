<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\AcademicYear;
use App\Models\Mark;
use Barryvdh\DomPDF\Facade\Pdf;
use Laravel\Sanctum\PersonalAccessToken;
        
class StudentResultController extends Controller
{
    public function examResults()
    {
        $user = auth()->user();
        $student = $user->student;

        $exams = Exam::where([
            'school_id' => $user->school_id,
            'class_id' => $student->class_id,
            'section_id' => $student->section_id,
            'is_result_published' => 1
        ])
        ->orderBy('start_date','desc')
        ->get([
            'id',
            'name',
            'start_date',
            'end_date'
        ]);

        return response()->json($exams);
    }
    public function examResultDetails($examId)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }

        $academicYearId = AcademicYear::where('school_id',$user->school_id)
            ->where('is_active',true)
            ->value('id');

        /* ===========================
        Get Exam
        =========================== */

        $exam = Exam::where([
            'id' => $examId,
            'school_id' => $user->school_id,
            'class_id' => $student->class_id,
            'section_id' => $student->section_id,
            'is_result_published' => 1
        ])->first();

        if(!$exam){
            return response()->json([
                'message' => 'Result not published yet'
            ],403);
        }

        /* ===========================
        Get Subjects of Exam
        =========================== */

        $subjects = \DB::table('exam_subjects')
            ->join('subjects','subjects.id','=','exam_subjects.subject_id')
            ->where('exam_subjects.exam_id',$examId)
            ->select(
                'subjects.id',
                'subjects.name',
                'exam_subjects.max_marks',
                'exam_subjects.pass_marks'
            )
            ->get();

        /* ===========================
        Get Student Marks
        =========================== */

        $marks = Mark::where([
            'school_id' => $user->school_id,
            'academic_year_id' => $academicYearId,
            'exam_id' => $examId,
            'student_id' => $student->id
        ])->get()->keyBy('subject_id');

        $resultSubjects = [];
        $total = 0;
        $maxTotal = 0;
        $failed = false;

        foreach($subjects as $subject){

            $mark = $marks->get($subject->id);

            $obtained = $mark->marks_obtained ?? null;

            if($obtained !== null){
                $total += $obtained;
            }

            $maxTotal += $subject->max_marks;

            if($obtained !== null && $obtained < $subject->pass_marks){
                $failed = true;
            }

            $resultSubjects[] = [
                'subject' => $subject->name,
                'marks_obtained' => $obtained,
                'max_marks' => $subject->max_marks,
                'pass_marks' => $subject->pass_marks
            ];
        }

        /* ===========================
        Calculate Result
        =========================== */

        $percentage = $maxTotal > 0
            ? round(($total / $maxTotal) * 100,2)
            : 0;

        $resultStatus = $failed ? 'Fail' : 'Pass';

        /* ===========================
        Calculate Rank
        =========================== */

        $totals = \DB::table('marks')
            ->select('student_id', \DB::raw('SUM(marks_obtained) as total'))
            ->where([
                'school_id' => $user->school_id,
                'academic_year_id' => $academicYearId,
                'exam_id' => $examId,
                'class_id' => $student->class_id,
                'section_id' => $student->section_id
            ])
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->get();

        $rank = null;

        foreach($totals as $index => $row){
            if($row->student_id == $student->id){
                $rank = $index + 1;
                break;
            }
        }

        /* ===========================
        Response
        =========================== */

        return response()->json([
            'exam' => $exam->name,
            'class_name' => $student->class->name ?? '',
            'section_name' => $student->section->name ?? '',
            'subjects' => $resultSubjects,
            'total_marks' => $total,
            'max_total' => $maxTotal,
            'percentage' => $percentage,
            'rank' => $rank,
            'result' => $resultStatus
        ]);
    }
   
    public function downloadMarksheet(Request $request, $examId)
    {

        /* ===============================
        TOKEN AUTHENTICATION
        =============================== */

        $token = $request->query('token');

        if(!$token){
            abort(401,'Unauthorized');
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if(!$accessToken){
            abort(401,'Invalid token');
        }

        $user = $accessToken->tokenable;

        $student = $user->student;

        if(!$student){
            abort(403,'Student not found');
        }

        /* ===============================
        GET ACTIVE ACADEMIC YEAR
        =============================== */

        $academicYearId = AcademicYear::where('school_id',$user->school_id)
            ->where('is_active',true)
            ->value('id');

        /* ===============================
        GET EXAM
        =============================== */

        $exam = Exam::findOrFail($examId);

        /* ===============================
        GET SUBJECTS
        =============================== */

        $subjects = \DB::table('exam_subjects')
            ->join('subjects','subjects.id','=','exam_subjects.subject_id')
            ->where('exam_subjects.exam_id',$examId)
            ->select(
                'subjects.name',
                'exam_subjects.max_marks',
                'exam_subjects.pass_marks',
                'subjects.id as subject_id'
            )
            ->get();

        /* ===============================
        GET MARKS
        =============================== */

        $marks = Mark::where([
            'school_id'=>$user->school_id,
            'academic_year_id'=>$academicYearId,
            'exam_id'=>$examId,
            'student_id'=>$student->id
        ])->get()->keyBy('subject_id');

        $resultSubjects = [];
        $total = 0;
        $maxTotal = 0;
        $failed = false;

        foreach($subjects as $subject){

            $mark = $marks->get($subject->subject_id);

            $obtained = $mark->marks_obtained ?? null;

            if($obtained !== null){
                $total += $obtained;
            }

            $maxTotal += $subject->max_marks;

            if($obtained !== null && $obtained < $subject->pass_marks){
                $failed = true;
            }

            $resultSubjects[] = [
                'subject'=>$subject->name,
                'marks'=>$obtained,
                'max_marks'=>$subject->max_marks,
                'pass_marks'=>$subject->pass_marks
            ];
        }

        /* ===============================
        CALCULATE PERCENTAGE
        =============================== */

        $percentage = $maxTotal > 0
            ? round(($total/$maxTotal)*100,2)
            : 0;

        $result = $failed ? "Fail" : "Pass";

        /* ===============================
        DATA FOR PDF
        =============================== */

        $data = [
            'student'=>$student,
            'exam'=>$exam,
            'subjects'=>$resultSubjects,
            'total'=>$total,
            'maxTotal'=>$maxTotal,
            'percentage'=>$percentage,
            'result'=>$result
        ];

        /* ===============================
        GENERATE PDF
        =============================== */

        $pdf = Pdf::loadView(
            'pdf.marksheet-pdf',
            $data
        );

        return $pdf->download(
            'marksheet_'.$exam->name.'.pdf'
        );
    }

}
