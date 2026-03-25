<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class TeacherExamController extends Controller
{
    public function exams(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $teacher = $user->teacher;

        if (!$teacher || !$teacher->is_active) {
            return response()->json([
                'message' => 'Invalid or inactive teacher'
            ], 403);
        }

        // Check teacher assignment exists
        $academicYearId = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->value('id');

        if (!$academicYearId) {
            return response()->json([
                'message' => 'No active academic year found'
            ], 422);
        }

        $hasAssignment = TeacherAssignment::where([
            'teacher_id'       => $teacher->id,
            'class_id'         => $data['class_id'],
            'section_id'       => $data['section_id'],
            'academic_year_id' => $academicYearId,
            'is_active'        => true,
        ])->exists();

        if (!$hasAssignment) {
            return response()->json([], 200);
        }

        $exams = Exam::where([
            'school_id'  => $user->school_id,
            'class_id'   => $data['class_id'],
            'section_id' => $data['section_id'],
            'is_active'  => true,
        ])
        ->where('status', 'published')   // ⭐ ADD THIS LINE
        ->orderBy('start_date', 'desc')
        ->get(['id', 'name', 'status']);

        return response()->json($exams);
    }

    public function subjects(Request $request, $examId)
    {
        $user = auth()->user();

        $data = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $teacher = $user->teacher;

        if (!$teacher || !$teacher->is_active) {
            return response()->json([
                'message' => 'Invalid or inactive teacher'
            ], 403);
        }

        // Get active academic year
        $academicYearId = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->value('id');

        // Subjects teacher teaches in that class/section
        $teacherSubjects = TeacherAssignment::where([
            'teacher_id'       => $teacher->id,
            'class_id'         => $data['class_id'],
            'section_id'       => $data['section_id'],
            'academic_year_id' => $academicYearId,
            'is_active'        => true,
        ])->pluck('subject_id');

        // Subjects included in exam
        $examSubjects = \DB::table('exam_subjects')
            ->where('exam_id', $examId)
            ->pluck('subject_id');

        // Intersection
        $subjectIds = $teacherSubjects->intersect($examSubjects);

        $subjects = \DB::table('exam_subjects')
            ->join('subjects', 'subjects.id', '=', 'exam_subjects.subject_id')
            ->where('exam_subjects.exam_id', $examId)
            ->whereIn('exam_subjects.subject_id', $subjectIds)
            ->select(
                'subjects.id',
                'subjects.name',
                'exam_subjects.max_marks',
                'exam_subjects.pass_marks'
            )
            ->get();

        return response()->json($subjects);
    }
    public function saveMarks(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required|array|min:1',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.marks_obtained' => 'nullable|numeric|min:0',
            'marks.*.is_absent' => 'boolean'
        ]);

        $teacher = $user->teacher;

        if (!$teacher || !$teacher->is_active) {
            return response()->json([
                'message' => 'Invalid teacher'
            ], 403);
        }

        // Prevent editing after result publish
        $exam = Exam::findOrFail($data['exam_id']);

        if ($exam->is_result_published) {
            return response()->json([
                'message' => 'Results already published. Marks cannot be modified.'
            ], 403);
        }

        $academicYearId = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->value('id');

        // Get subject exam configuration
        $examSubject = \DB::table('exam_subjects')
            ->where('exam_id', $data['exam_id'])
            ->where('subject_id', $data['subject_id'])
            ->first();

        if (!$examSubject) {
            return response()->json([
                'message' => 'Subject not part of this exam'
            ], 422);
        }

        $created = false;
        $updated = false;

        foreach ($data['marks'] as $entry) {

            if (!$entry['is_absent'] && $entry['marks_obtained'] > $examSubject->max_marks) {
                return response()->json([
                    'message' => 'Marks cannot exceed max marks'
                ], 422);
            }

            $existingMark = Mark::where([
                'school_id' => $user->school_id,
                'academic_year_id' => $academicYearId,
                'exam_id' => $data['exam_id'],
                'class_id' => $data['class_id'],
                'section_id' => $data['section_id'],
                'subject_id' => $data['subject_id'],
                'student_id' => $entry['student_id'],
            ])->first();

            if ($existingMark) {

                $existingMark->update([
                    'marks_obtained' => $entry['is_absent'] ? null : $entry['marks_obtained'],
                    'is_absent' => $entry['is_absent'] ?? false
                ]);

                $updated = true;

            } else {

                Mark::create([
                    'school_id' => $user->school_id,
                    'academic_year_id' => $academicYearId,
                    'exam_id' => $data['exam_id'],
                    'class_id' => $data['class_id'],
                    'section_id' => $data['section_id'],
                    'subject_id' => $data['subject_id'],
                    'student_id' => $entry['student_id'],
                    'marks_obtained' => $entry['is_absent'] ? null : $entry['marks_obtained'],
                    'is_absent' => $entry['is_absent'] ?? false
                ]);

                $created = true;
            }
        }

        $message = $updated ? 'Marks updated successfully' : 'Marks submitted successfully';

        return response()->json([
            'message' => $message
        ]);
    }
    
    public function marksSheet(Request $request, $examId)
    {
        $user = auth()->user();

        $data = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacher = $user->teacher;

        if (!$teacher || !$teacher->is_active) {
            return response()->json([
                'message' => 'Invalid teacher'
            ], 403);
        }

        $academicYearId = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->value('id');

        // Get exam subject details
        $examSubject = \DB::table('exam_subjects')
            ->where('exam_id', $examId)
            ->where('subject_id', $data['subject_id'])
            ->first();

        if (!$examSubject) {
            return response()->json([
                'message' => 'Subject not part of this exam'
            ], 422);
        }

        // Fetch students
        $students = \App\Models\Student::where([
            'school_id' => $user->school_id,
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'is_active' => true,
        ])
        ->orderBy('name')
        ->get();

        $marks = \App\Models\Mark::where([
            'school_id' => $user->school_id,
            'academic_year_id' => $academicYearId,
            'exam_id' => $examId,
            'subject_id' => $data['subject_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
        ])->get()->keyBy('student_id');

        $studentsData = $students->map(function ($student) use ($marks) {

            $mark = $marks->get($student->id);

            return [
                'student_id' => $student->id,
                'admission_no' => $student->admission_no,
                'name' => $student->name,
                'marks_obtained' => $mark->marks_obtained ?? null,
                'is_absent' => $mark->is_absent ?? false,
            ];
        });

        return response()->json([
            'exam_id' => (int)$examId,
            'subject_id' => $data['subject_id'],
            'max_marks' => $examSubject->max_marks,
            'pass_marks' => $examSubject->pass_marks,
            'students' => $studentsData
        ]);
    }
}
