<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class TeacherPortalController extends Controller
{
    public function me()
    {
        $user = auth()->user();

        $teacher = Teacher::with(['subjects:id,name'])
            ->where('user_id', $user->id)
            ->where('school_id', $user->school_id) // 🔒 added
            ->where('is_active', true)
            ->first();

        if (! $teacher) {
            return response()->json([
                'message' => 'Teacher profile not found or inactive'
            ], 404);
        }

        return response()->json([
            'id'               => $teacher->id,
            'name'             => $teacher->name,
            'email'            => $teacher->email,
            'phone'            => $teacher->phone,
            'employee_code'    => $teacher->employee_code,
            'gender'           => $teacher->gender,
            'date_of_joining'  => $teacher->date_of_joining,
            'date_of_birth'    => $teacher->date_of_birth,
            'address'          => $teacher->address,
            'qualification'    => $teacher->qualification,
            'experience_years' => $teacher->experience_years,
            'primary_subject'  => $teacher->primary_subject,
            'secondary_subject'=> $teacher->secondary_subject,
            'school_id'        => $teacher->school_id,
            'subjects'         => $teacher->subjects,
        ]);
    }

    /**
     * Teacher dashboard summary
     */
    public function dashboard()
    {
        $user = auth()->user();

        $teacher = Teacher::where('user_id', $user->id)
            ->where('school_id', $user->school_id) // 🔒 added
            ->where('is_active', true)
            ->firstOrFail();

        $assignments = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('school_id', $user->school_id) // 🔒 added
            ->where('is_active', true)
            ->get();

        return response()->json([
            'teacher_name' => $teacher->name,
            'assigned_classes_count' => $assignments->pluck('class_id')->unique()->count(),
            'assigned_subjects_count' => $assignments->pluck('subject_id')->unique()->count(),
            'pending_attendance' => 0,
        ]);
    }
    public function students(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'class_id'   => 'required|integer',
            'section_id' => 'required|integer',
        ]);

        // Resolve teacher
        $teacher = Teacher::where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Resolve active academic year
        $academicYear = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Check class teacher assignment using teacher_assignments table
        $isAssigned = TeacherAssignment::where([
            'school_id'        => $user->school_id,
            'academic_year_id' => $academicYear->id,
            'teacher_id'       => $teacher->id,
            'class_id'         => $request->class_id,
            'section_id'       => $request->section_id,
            'is_class_teacher' => 1,
            'is_active'        => 1,
        ])->exists();

        if (! $isAssigned) {
            return response()->json([
                'message' => 'Only class teacher can load students'
            ], 403);
        }

        // Fetch students
        $students = Student::where('school_id', $user->school_id)
            ->where('academic_year_id', $academicYear->id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'admission_no',
                'name',
                'gender',
                'date_of_joining',
            ]);

        return response()->json($students);
    }

    /**
     * Teacher classes (CORE API)
     */
    public function classes()
    {
        $user = auth()->user();

        $teacher = Teacher::where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        $assignments = TeacherAssignment::with([
                'class:id,name',
                'section:id,name',
                'subject:id,name',
                'academicYear:id,name'
            ])
            ->where('teacher_id', $teacher->id)
            ->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->get();

        return response()->json(
            $assignments->map(function ($a) {

                return [
                    'class_id'     => $a->class->id ?? null,
                    'class_name'   => $a->class->name ?? '-',

                    'section_id'   => $a->section->id ?? null,
                    'section_name' => $a->section->name ?? '-',

                    // 🔥 Handle class teacher case
                    'subject_id'   => $a->is_class_teacher ? null : ($a->subject->id ?? null),

                    'subject_name' => $a->is_class_teacher
                                        ? 'Class Teacher'
                                        : ($a->subject->name ?? '-'),

                    'academic_year' => $a->academicYear->name ?? '-',

                    'is_class_teacher' => $a->is_class_teacher,
                ];
            })
        );
    }
    public function assignClassTeacher(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'teacher_id' => 'required|exists:teachers,id'
        ]);

        $schoolId = $user->school_id;

        $academicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->firstOrFail();

        // NEW CHECK
        $alreadyAssigned = TeacherAssignment::where([
            'school_id'        => $schoolId,
            'academic_year_id' => $academicYear->id,
            'teacher_id'       => $validated['teacher_id'],
            'is_class_teacher' => 1,
            'is_active'        => 1
        ])->first();

        if ($alreadyAssigned) {

            return response()->json([
                'message' => 'This teacher is already assigned as class teacher for another class.'
            ], 422);
        }

        // Deactivate existing class teacher for this class-section
        TeacherAssignment::where([
            'school_id'        => $schoolId,
            'academic_year_id' => $academicYear->id,
            'class_id'         => $validated['class_id'],
            'section_id'       => $validated['section_id'],
            'is_class_teacher' => 1,
            'is_active'        => 1
        ])->update(['is_active' => 0]);

        // Create new assignment
        TeacherAssignment::create([
            'school_id'        => $schoolId,
            'academic_year_id' => $academicYear->id,
            'teacher_id'       => $validated['teacher_id'],
            'class_id'         => $validated['class_id'],
            'section_id'       => $validated['section_id'],
            'subject_id'       => null,
            'is_class_teacher' => 1,
            'is_active'        => 1
        ]);

        return response()->json([
            'message' => 'Class teacher assigned successfully'
        ]);
    }
    
    public function classTeacherClasses()
    {
        $user = auth()->user();

        $teacher = Teacher::where('user_id', $user->id)
            ->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->firstOrFail();

        $academicYear = AcademicYear::where('school_id', $user->school_id)
            ->where('is_active', 1)
            ->firstOrFail();

        $assignments = TeacherAssignment::with([
                'class:id,name',
                'section:id,name'
            ])
            ->where('teacher_id', $teacher->id)
            ->where('school_id', $user->school_id)
            ->where('academic_year_id', $academicYear->id)
            ->where('is_class_teacher', 1)
            ->where('is_active', 1)
            ->get();

        return response()->json(
            $assignments->map(function ($a) {
                return [
                    'class_id'     => $a->class->id,
                    'class_name'   => $a->class->name,
                    'section_id'   => $a->section->id,
                    'section_name' => $a->section->name,
                ];
            })
        );
    }

    public function getAllocations(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $schoolId = $user->school_id;

        $academicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->firstOrFail();

        // Get class teacher
        $classTeacher = TeacherAssignment::with('teacher')
            ->where([
                'school_id'        => $schoolId,
                'class_id'         => $validated['class_id'],
                'section_id'       => $validated['section_id'],
                'academic_year_id' => $academicYear->id,
                'is_class_teacher' => 1,
                'is_active'        => 1
            ])
            ->first();

        // Get subject teachers
        $subjectTeachers = TeacherAssignment::with(['teacher', 'subject'])
            ->where([
                'school_id'        => $schoolId,
                'class_id'         => $validated['class_id'],
                'section_id'       => $validated['section_id'],
                'academic_year_id' => $academicYear->id,
                'is_class_teacher' => 0,
                'is_active'        => 1
            ])
            ->whereNotNull('subject_id') // VERY IMPORTANT
            ->get();

        return response()->json([
            'class_teacher'    => $classTeacher,
            'subject_teachers' => $subjectTeachers
        ]);
    }

    public function assignSubjectTeachers(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'allocations' => 'required|array|min:1',
            'allocations.*.subject_id' => 'required|exists:subjects,id',
            'allocations.*.teacher_id' => 'required|exists:teachers,id',
        ]);

        $schoolId = $user->school_id;

        $academicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_active', true)
            ->firstOrFail();

        try {

            foreach ($validated['allocations'] as $allocation) {

                $existing = TeacherAssignment::where([
                    'school_id'        => $schoolId,
                    'academic_year_id' => $academicYear->id,
                    'class_id'         => $validated['class_id'],
                    'section_id'       => $validated['section_id'],
                    'subject_id'       => $allocation['subject_id'],
                    'is_class_teacher' => 0,
                    'is_active'        => 1
                ])->first();

                if ($existing) {

                    // If same teacher → skip
                    if ($existing->teacher_id == $allocation['teacher_id']) {
                        continue;
                    }

                    // If different teacher → update
                    $existing->update([
                        'teacher_id' => $allocation['teacher_id']
                    ]);

                } else {

                    // No existing → create new
                    TeacherAssignment::create([
                        'school_id'        => $schoolId,
                        'academic_year_id' => $academicYear->id,
                        'teacher_id'       => $allocation['teacher_id'],
                        'class_id'         => $validated['class_id'],
                        'section_id'       => $validated['section_id'],
                        'subject_id'       => $allocation['subject_id'],
                        'is_class_teacher' => 0,
                        'is_active'        => 1
                    ]);
                }
            }

            return response()->json([
                'message' => 'Subject teachers updated successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to assign subject teachers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
