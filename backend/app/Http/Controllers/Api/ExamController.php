<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\ExamSubject;
use App\Models\Mark;

class ExamController extends Controller
{
    // ================================
    // CREATE EXAM (Admin)
    // ================================
    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'name'       => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $exam = Exam::create([
            'school_id'         => auth()->user()->school_id,
            'academic_year_id' => currentAcademicYearId(),
            'class_id'          => $data['class_id'],
            'section_id'        => $data['section_id'],
            'name'              => $data['name'],
            'start_date'         => $data['start_date'],
            'end_date'             => $data['end_date'],
            'status'            => 'draft',
            'is_active'         => true,
            'is_result_published' => false,
        ]);

        return response()->json($exam, 201);
    }

    // ================================
    // LIST EXAMS
    // ================================
    public function index(Request $request)
    {
        $query = Exam::where('school_id', auth()->user()->school_id);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->orderBy('start_date')->paginate(10)
        );
    }

    // ================================
    // PUBLISH EXAM
    // ================================
    public function publish($id)
    {
        $exam = Exam::findOrFail($id);

        if ($exam->status === 'published') {
            return response()->json(['message' => 'Exam already published'], 400);
        }

        $exam->update(['status' => 'published']);

        return response()->json(['message' => 'Exam published successfully']);
    }

    // ================================
    // DEACTIVATE EXAM
    // ================================
    public function deactivate($id)
    {
        $exam = Exam::findOrFail($id);

        $exam->update(['is_active' => false]);

        return response()->json(['message' => 'Exam deactivated successfully']);
    }

    

    // ================================
    // PUBLISH RESULTS
    // ================================
    public function publishResults($examId)
    {
        $user = auth()->user();

        $exam = Exam::where('school_id', $user->school_id)
            ->findOrFail($examId);

        if ($exam->is_result_published) {
            return response()->json([
                'message' => 'Results already published'
            ], 422);
        }

        $exam->update([
            'is_result_published' => true
        ]);

        return response()->json([
            'message' => 'Results published successfully'
        ]);
    }

    public function studentExams(Request $request)
    {
        $user = auth()->user();

        // 1️⃣ Resolve logged-in student
        $student = Student::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 2️⃣ Fetch exams for student's class & section
        $query = Exam::query()
            ->where('school_id', $student->school_id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('is_active', true)
            ->where('is_result_published', true)
            ->with([
                'academicYear:id,name',
                'class:id,name',
                'section:id,name',
            ])
            ->orderByDesc('start_date');

        // 3️⃣ Optional academic year filter (for previous years)
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $exams = $query->get();

        // 4️⃣ Group exams by academic year (UI friendly)
        $grouped = $exams->groupBy(function ($exam) {
            return $exam->academicYear->name;
        })->map(function ($items) {
            return $items->map(function ($exam) {
                return [
                    'exam_id'     => $exam->id,
                    'exam_name'   => $exam->name,
                    'start_date'  => $exam->start_date,
                    'end_date'    => $exam->end_date,
                    'class'       => $exam->class->name,
                    'section'     => $exam->section->name,
                ];
            });
        });

        return response()->json([
            'student' => [
                'id'   => $student->id,
                'name' => $student->name,
            ],
            'exams_by_academic_year' => $grouped,
        ]);
    }

    public function studentExamResults($examId)
    {
        $user = auth()->user();

        // 1️ Resolve logged-in student
        $student = Student::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 2️ Validate exam for this student
        $exam = Exam::where('id', $examId)
            ->where('school_id', $student->school_id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('is_active', true)
            ->where('is_result_published', true)
            ->firstOrFail();

        // 3️ Fetch subject-wise results
        $results = \DB::table('exam_subjects')
            ->join('subjects', 'subjects.id', '=', 'exam_subjects.subject_id')
            ->leftJoin('marks', function ($join) use ($student, $exam) {
                $join->on('marks.subject_id', '=', 'exam_subjects.subject_id')
                    ->where('marks.exam_id', $exam->id)
                    ->where('marks.student_id', $student->id);
            })
            ->where('exam_subjects.exam_id', $exam->id)
            ->select(
                'subjects.name as subject',
                'exam_subjects.max_marks',
                'exam_subjects.pass_marks',
                'marks.marks_obtained',
                'marks.is_absent'
            )
            ->orderBy('subjects.name')
            ->get();

        // 4 Transform for frontend (CORRECT LOGIC)
        $formatted = $results->map(function ($row) {

            // Case 1: Marks not uploaded yet
            if (is_null($row->marks_obtained) && is_null($row->is_absent)) {
                return [
                    'subject'        => $row->subject,
                    'marks_obtained' => null,
                    'max_marks'      => $row->max_marks,
                    'status'         => 'Pending'
                ];
            }

            //  Case 2: Student absent
            if ($row->is_absent) {
                return [
                    'subject'        => $row->subject,
                    'marks_obtained' => null,
                    'max_marks'      => $row->max_marks,
                    'status'         => 'Absent'
                ];
            }

            //  Case 3: Marks available → Pass / Fail
            return [
                'subject'        => $row->subject,
                'marks_obtained' => $row->marks_obtained,
                'max_marks'      => $row->max_marks,
                'status'         => ($row->marks_obtained >= $row->pass_marks) ? 'Pass' : 'Fail'
            ];
        });

        return response()->json([
            'exam' => [
                'id'   => $exam->id,
                'name' => $exam->name,
            ],
            'results' => $formatted
        ]);
    }
    public function studentExamSummary()
    {
        $user = auth()->user();

        // 1️ Resolve student
        $student = Student::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 2️ Get all published exams for this student
        $exams = Exam::where('school_id', $student->school_id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('is_active', true)
            ->where('is_result_published', true)
            ->get();

        $summary = [];

        foreach ($exams as $exam) {

            // Total subjects mapped to exam
            $totalSubjects = \DB::table('exam_subjects')
                ->where('exam_id', $exam->id)
                ->count();

            // Marks uploaded for this student
            $marks = \DB::table('marks')
                ->where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->get();

            $passed = 0;
            $failed = 0;

            foreach ($marks as $mark) {
                $passMarks = \DB::table('exam_subjects')
                    ->where('exam_id', $exam->id)
                    ->where('subject_id', $mark->subject_id)
                    ->value('pass_marks');

                if ($mark->is_absent) {
                    $failed++;
                } elseif ($mark->marks_obtained >= $passMarks) {
                    $passed++;
                } else {
                    $failed++;
                }
            }

            $pending = $totalSubjects - ($passed + $failed);

            $summary[] = [
                'exam_id'        => $exam->id,
                'exam_name'      => $exam->name,
                'total_subjects' => $totalSubjects,
                'passed'         => $passed,
                'failed'         => $failed,
                'pending'        => $pending
            ];
        }

        return response()->json([
            'student' => [
                'id'   => $student->id,
                'name' => $student->name
            ],
            'exams' => $summary
        ]);
    }

    public function studentExamList()
    {
        $user = auth()->user();

        // 1️ Resolve student
        $student = Student::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 2️ Fetch exams
        $exams = Exam::where('school_id', $student->school_id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('is_active', true)
            ->with('academicYear:id,name')
            ->orderBy('start_date', 'desc')
            ->get();

        // 3️ Transform exams
        $formatted = $exams->map(function ($exam) {

            if ($exam->is_result_published) {
                $status = 'Result Published';
            } elseif ($exam->status === 'published') {
                $status = 'Published';
            } else {
                $status = 'Draft';
            }

            return [
                'exam_id'         => $exam->id,
                'exam_name'       => $exam->name,
                'start_date'      => $exam->start_date,
                'status'          => $status,
                'can_view_result' => $exam->is_result_published
            ];
        });

        // 4️ Group by academic year
        $grouped = $formatted->groupBy(function ($item) use ($exams) {
            return $exams
                ->firstWhere('id', $item['exam_id'])
                ->academicYear
                ->name;
        });

        return response()->json([
            'student' => [
                'id'   => $student->id,
                'name' => $student->name
            ],
            'exams_by_academic_year' => $grouped
        ]);
    }

    public function byClassSection(Request $request, $classId, $sectionId)
    {
        $user = $request->user();

        $exams = Exam::where('school_id', $user->school_id)
            ->where('academic_year_id', currentAcademicYearId())
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('is_active', 1)
            ->orderBy('start_date', 'desc')
            ->get([
                'id',
                'name',
                'start_date',
                'end_date',
                'status',
                'is_result_published'
            ]);

        return response()->json($exams);
    }
    public function show($id)
    {
        return Exam::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        if ($exam->is_result_published) {
            return response()->json([
                'message' => 'Cannot edit exam after results are published'
            ], 403);
        }

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'required|in:draft,published',
        ]);

        $exam->update($data);

        return response()->json([
            'message' => 'Exam updated successfully',
            'exam'    => $exam
        ]);
    }
    // reusable function to check exam lock status
    private function getExamLockStatus(Exam $exam): array
    {
        // 1. Results published → hard lock
        if ($exam->is_result_published) {
            return [
                'locked' => true,
                'reason' => 'results_published',
                'message' => 'Subjects cannot be modified after results are published.'
            ];
        }

        // 2. Marks entry started → soft lock
        $marksExist = Mark::where('exam_id', $exam->id)->exists();

        if ($marksExist) {
            return [
                'locked' => true,
                'reason' => 'marks_started',
                'message' => 'Subjects cannot be modified after marks entry has started.'
            ];
        }

        // Not locked
        return [
            'locked' => false,
            'reason' => null,
            'message' => null
        ];
    }

    // ================================
    // MAP SUBJECTS TO EXAM
    // ================================
    public function mapSubjects(Request $request, $examId)
    {
        $data = $request->validate([
            'subjects' => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.max_marks'  => 'required|integer|min:1',
            'subjects.*.pass_marks' => 'required|integer|min:0',
        ]);

        $exam = Exam::findOrFail($examId);

        $lock = $this->getExamLockStatus($exam);

        if ($lock['locked']) {
            return response()->json([
                'message' => $lock['message'],
                'reason'  => $lock['reason']
            ], 403);
        }

        $subjectIds = collect($data['subjects'])
            ->pluck('subject_id')
            ->toArray();

        // 🔥 Remove subjects not selected anymore
        ExamSubject::where('exam_id', $exam->id)
            ->whereNotIn('subject_id', $subjectIds)
            ->delete();

        foreach ($data['subjects'] as $subject) {

            if ($subject['pass_marks'] > $subject['max_marks']) {
                return response()->json([
                    'message' => 'Pass marks cannot exceed max marks'
                ], 422);
            }

            ExamSubject::updateOrCreate(
                [
                    'exam_id'    => $exam->id,
                    'subject_id' => $subject['subject_id']
                ],
                [
                    'max_marks'  => $subject['max_marks'],
                    'pass_marks' => $subject['pass_marks']
                ]
            );
        }

        return response()->json([
            'message' => 'Subjects mapped successfully'
        ]);
    }

    public function getMappedSubjects($examId)
    {
        $exam = Exam::findOrFail($examId);

        $lock = $this->getExamLockStatus($exam);

        $mappedSubjects = ExamSubject::with('subject')
            ->where('exam_id', $examId)
            ->get();

        return response()->json([
            'locked' => $lock['locked'],
            'reason' => $lock['reason'],
            'message' => $lock['message'],
            'subjects' => $mappedSubjects
        ]);
    }
    public function resultsByClassSection($classId, $sectionId)
    {
        $user = auth()->user();

        $exams = Exam::where([
            'school_id'  => $user->school_id,
            'class_id'   => $classId,
            'section_id' => $sectionId,
            'is_active'  => true
        ])->get();

        $data = [];

        foreach ($exams as $exam) {

            // total students
            $students = Student::where([
                'school_id' => $user->school_id,
                'academic_year_id' => $user->academic_year_id,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'is_active' => true
            ])->count();

            // subjects in this exam
            $subjects = ExamSubject::where('exam_id', $exam->id)->count();

            $marksExpected = $students * $subjects;

            // marks already entered
            $marksEntered = Mark::where([
                'school_id' => $user->school_id,
                'academic_year_id' => $user->academic_year_id,
                'exam_id' => $exam->id,
                'class_id' => $classId,
                'section_id' => $sectionId
            ])->count();

            $data[] = [
                'exam_id' => $exam->id,
                'exam_name' => $exam->name,
                'start_date' => $exam->start_date,
                'end_date' => $exam->end_date,
                'marks_entered' => $marksEntered,
                'marks_expected' => $marksExpected,
                'marks_status' => $marksEntered == $marksExpected ? 'Completed' : 'Pending',
                'is_result_published' => $exam->is_result_published
            ];
        }

        return response()->json($data);
    }

    public function viewResults($examId)
    {
        $user = auth()->user();

        $exam = Exam::with(['class','section'])
            ->where('school_id', $user->school_id)
            ->findOrFail($examId);

        // Subjects in this exam
        $subjects = ExamSubject::with('subject')
            ->where('exam_id', $examId)
            ->get();

        // Students in this class + section
        $students = Student::where([
            'school_id' => $user->school_id,
            'class_id' => $exam->class_id,
            'section_id' => $exam->section_id,
            'is_active' => 1
        ])
        ->orderBy('name')
        ->get();

        // All marks of this exam
        $marks = Mark::where([
            'exam_id' => $examId,
            'school_id' => $user->school_id
        ])
        ->get()
        ->groupBy('student_id');

        $results = [];

        foreach ($students as $student) {

            $studentMarks = [];
            $total = 0;
            $failed = false;

            foreach ($subjects as $subject) {

                $studentMarksCollection = $marks->get($student->id, collect());

                $mark = $studentMarksCollection
                    ->where('subject_id', $subject->subject_id)
                    ->first();
                    
                if (!$mark) {
                    $value = null;
                }
                elseif ($mark->is_absent) {
                    $value = 'Absent';
                    $failed = true;
                }
                else {
                    $value = $mark->marks_obtained;
                    $total += $mark->marks_obtained;

                    if ($mark->marks_obtained < $subject->pass_marks) {
                        $failed = true;
                    }
                }

                $studentMarks[$subject->subject_id] = $value;
            }

            $results[] = [
                'student_id' => $student->id,
                'name' => $student->name,
                'admission_no' => $student->admission_no,
                'marks' => $studentMarks,
                'total' => $total,
                'result' => $failed ? 'Fail' : 'Pass'
            ];
        }

        /*
        =========================================
        SORT STUDENTS BY TOTAL MARKS (DESC)
        =========================================
        */

        usort($results, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        /*
        =========================================
        RANK CALCULATION (COMPETITION RANKING)
        Example: 1,2,2,4
        =========================================
        */

        $rank = 0;
        $previousMarks = null;
        $position = 0;

        foreach ($results as &$student) {

            $position++;

            if ($student['total'] !== $previousMarks) {
                $rank = $position;
            }

            $student['rank'] = $rank;

            $previousMarks = $student['total'];
        }

        return response()->json([
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'class' => $exam->class->name,
                'section' => $exam->section->name,
                'start_date' => $exam->start_date,
                'end_date' => $exam->end_date,
                'is_result_published' => $exam->is_result_published
            ],
            'subjects' => $subjects->map(function ($s) {
                return [
                    'subject_id' => $s->subject_id,
                    'subject_name' => $s->subject->name,
                    'max_marks' => $s->max_marks,
                    'pass_marks' => $s->pass_marks
                ];
            }),
            'students' => $results
        ]);
    }
}
