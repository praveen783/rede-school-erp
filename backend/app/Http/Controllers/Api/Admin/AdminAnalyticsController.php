<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Mark;

class AdminAnalyticsController extends Controller
{
    public function classAnalytics(Request $request)
    {
        /* -----------------------------------------
        VALIDATE REQUEST PARAMETERS
        ----------------------------------------- */

        $data = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $classId = $data['class_id'];
        $sectionId = $data['section_id'];
        $academicYearId = $data['academic_year_id'];

        /* -----------------------------------------
        STUDENTS BASIC DETAILS
        ----------------------------------------- */

        $students = Student::with('category')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('academic_year_id', $academicYearId)
            ->get([
                'id',
                'admission_no',
                'name',
                'parent_name',
                'gender',
                'category_id',
                'is_active'
            ]);

        $totalStudents = $students->count();

        /* -----------------------------------------
        GENDER STATS
        ----------------------------------------- */

        $genderStats = Student::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('academic_year_id', $academicYearId)
            ->selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender');

        /* -----------------------------------------
        CATEGORY STATS
        ----------------------------------------- */

        $categoryStats = Student::leftJoin('categories', 'students.category_id', '=', 'categories.id')
            ->where('students.class_id', $classId)
            ->where('students.section_id', $sectionId)
            ->where('students.academic_year_id', $academicYearId)
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        /* -----------------------------------------
        ATTENDANCE OVERVIEW
        ----------------------------------------- */

        $attendanceStats = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
            ->where('students.class_id', $classId)
            ->where('students.section_id', $sectionId)
            ->where('students.academic_year_id', $academicYearId)
            ->selectRaw('
                SUM(CASE WHEN attendances.status = "present" THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN attendances.status = "absent" THEN 1 ELSE 0 END) as total_absent,
                COUNT(*) as total_records
            ')
            ->first();

        $attendancePercentage = 0;

        if ($attendanceStats && $attendanceStats->total_records > 0) {
            $attendancePercentage = round(
                ($attendanceStats->total_present / $attendanceStats->total_records) * 100,
                2
            );
        }

        $attendanceSummary = [
            'total_present' => $attendanceStats->total_present ?? 0,
            'total_absent' => $attendanceStats->total_absent ?? 0,
            'total_records' => $attendanceStats->total_records ?? 0,
            'attendance_percentage' => $attendancePercentage
        ];

        /* -----------------------------------------
        TODAY ATTENDANCE STUDENT LIST
        ----------------------------------------- */

        $attendanceStudents = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
            ->where('students.class_id', $classId)
            ->where('students.section_id', $sectionId)
            ->where('students.academic_year_id', $academicYearId)
            ->select(
                'students.admission_no',
                'students.name',
                'attendances.status'
            )
            ->orderBy('students.name')
            ->get();

        /* -----------------------------------------
        EXAM PERFORMANCE (TOTAL PER EXAM)
        ----------------------------------------- */

        $examResults = Mark::join('students', 'marks.student_id', '=', 'students.id')
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->where('students.class_id', $classId)
            ->where('students.section_id', $sectionId)
            ->where('students.academic_year_id', $academicYearId)
            ->selectRaw('
                students.id as student_id,
                students.name as student,
                exams.id as exam_id,
                exams.name as exam,
                SUM(marks.marks_obtained) as total_marks
            ')
            ->groupBy(
                'students.id',
                'students.name',
                'exams.id',
                'exams.name'
            )
            ->orderBy('students.name')
            ->get();

        /* -----------------------------------------
        FINAL RESPONSE
        ----------------------------------------- */

        return response()->json([
            'summary' => [
                'total_students' => $totalStudents
            ],
            'students' => $students,
            'gender_stats' => $genderStats,
            'category_stats' => $categoryStats,
            'attendance_summary' => $attendanceSummary,
            'attendance_students' => $attendanceStudents,
            'exam_results' => $examResults
        ]);
    }

    public function studentDetails(Request $request)
    {

        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $studentId = $request->student_id;

        /* -----------------------------------------
        STUDENT BASIC DETAILS
        ----------------------------------------- */

        $student = Student::with('category')->findOrFail($studentId);


        /* -----------------------------------------
        ATTENDANCE PERCENTAGE
        ----------------------------------------- */

        $attendanceStats = Attendance::where('student_id', $studentId)
            ->selectRaw('
                SUM(CASE WHEN status="present" THEN 1 ELSE 0 END) as present,
                COUNT(*) as total
            ')
            ->first();

        $attendancePercentage = 0;

        if ($attendanceStats && $attendanceStats->total > 0) {
            $attendancePercentage = round(
                ($attendanceStats->present / $attendanceStats->total) * 100,
                2
            );
        }


        /* -----------------------------------------
        SUBJECT WISE MARKS
        ----------------------------------------- */

        $marks = Mark::join('subjects', 'marks.subject_id', '=', 'subjects.id')
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->where('marks.student_id', $studentId)
            ->select(
                'exams.name as exam',
                'subjects.name as subject',
                'marks.marks_obtained as marks'
            )
            ->get();


        /* -----------------------------------------
        TOTAL MARKS
        ----------------------------------------- */

        $totalMarks = Mark::where('student_id', $studentId)
            ->sum('marks_obtained');


        /* -----------------------------------------
        STUDENT RANK IN CLASS
        ----------------------------------------- */

        $classRanks = Mark::join('students', 'marks.student_id', '=', 'students.id')
            ->where('students.class_id', $student->class_id)
            ->where('students.section_id', $student->section_id)
            ->where('students.academic_year_id', $student->academic_year_id)
            ->selectRaw('students.id, SUM(marks.marks_obtained) as total_marks')
            ->groupBy('students.id')
            ->orderByDesc('total_marks')
            ->get();

        $rank = 1;

        foreach ($classRanks as $index => $row) {
            if ($row->id == $studentId) {
                $rank = $index + 1;
                break;
            }
        }

        $totalClassStudents = $classRanks->count();


        /* -----------------------------------------
        ATTENDANCE TREND (MONTHLY)
        ----------------------------------------- */

       $attendanceTrend = Attendance::where('student_id', $studentId)
        ->where('academic_year_id', $student->academic_year_id)
        ->selectRaw('
            MONTH(attendance_date) as month,
            SUM(CASE WHEN status="present" THEN 1 ELSE 0 END) as present,
            COUNT(*) as total
        ')
        ->groupByRaw('MONTH(attendance_date)')
        ->orderByRaw('MONTH(attendance_date)')
        ->get();

        /* -----------------------------------------
        RESPONSE
        ----------------------------------------- */

        return response()->json([

            'student' => $student,

            'attendance_percentage' => $attendancePercentage,

            'total_marks' => $totalMarks,

            'rank' => $rank,

            'class_total_students' => $totalClassStudents,

            'attendance_trend' => $attendanceTrend,

            'marks' => $marks

        ]);

    }


}