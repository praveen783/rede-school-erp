<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAdmitCard;
use App\Models\Student;
use App\Models\ExamSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ExamAdmitCardController extends Controller
{
    /**
     * Create Admit Card (Draft)
     */
   public function store(Request $request, Exam $exam)
    {
        $request->validate([
            'class_id'   => 'required|integer',
            'section_id' => 'required|integer',
        ]);

        // Check students exist
        $studentsExist = Student::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('is_active', 1)
            ->exists();

        if (!$studentsExist) {
            abort(422, 'No students found');
        }

        // Create / Update single class-wise admit card
        ExamAdmitCard::updateOrCreate(
            [
                'school_id'        => auth()->user()->school_id,
                'academic_year_id' => currentAcademicYearId(),
                'exam_id'          => $exam->id,
                'class_id'         => $request->class_id,
                'section_id'       => $request->section_id,
            ],
            [
                'status'     => 'Draft',
                'created_by' => auth()->user()->id,
            ]
        );

        return response()->json([
            'message' => 'Admit card configuration created successfully'
        ]);
    }
    
    /**
     * Publish Admit Card (LOCK)
     */
    public function publish(Request $request, Exam $exam)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
        ]);

        $updated = ExamAdmitCard::where([
            'exam_id'    => $exam->id,
            'class_id'   => $request->class_id,
            'section_id' => $request->section_id,
            'school_id'  => auth()->user()->school_id,
        ])->update([
            'status' => 'Published',
        ]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'No admit cards found to publish'
            ], 404);
        }

        return response()->json([
            'message' => 'Admit cards published successfully'
        ]);
    }

    /**
 * Preview Admit Card (JSON)
 */
    public function preview(Exam $exam, Student $student)
    {
        // School isolation
        if ($exam->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized access');
        }

        // Student eligibility check
        if (
            $student->school_id !== $exam->school_id ||
            $student->class_id !== $exam->class_id ||
            $student->section_id !== $exam->section_id ||
            !$student->is_active
        ) {
            abort(422, 'Student not eligible for this exam');
        }

        $admitCard = ExamAdmitCard::where('exam_id', $exam->id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('academic_year_id', currentAcademicYearId())
            ->where('status', 'Published')
            ->firstOrFail();


        // Fetch subject-wise schedules
       $schedules = ExamSchedule::with('subject')
            ->where('exam_id', $exam->id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->orderBy('exam_date')
            ->get();

        return response()->json([
            'admit_card_no' => 'ADM-' . currentAcademicYearId() . '-' . $exam->id . '-' . $student->id,

            'school' => [
                'name'    => $exam->school->name,
                'address' => $exam->school->address,
                'logo'    => $exam->school->logo_path,
            ],

            'student' => [
                'name'         => $student->name,
                'admission_no' => $student->admission_no,
                'father_name'  => $student->parent_name,
                'dob'          => $student->dob,
                'photo'        => $student->photo_path,
            ],

            'exam' => [
                'name'    => $exam->name,
                'class'   => $exam->class->name,
                'section' => $exam->section->name,
            ],

            'subjects' => $schedules->map(function ($row) {
                return [
                    'subject' => $row->subject->name,
                    'date'    => $row->exam_date,
                    'time'    => $row->start_time . ' - ' . $row->end_time,
                ];
            }),
        ]);
    }
    /**
 * Download Admit Card PDF
 */
    public function pdf(Exam $exam, Student $student)
    {
        // Reuse preview data
        $previewResponse = $this->preview($exam, $student);
        $data = $previewResponse->getData(true);

        $pdf = Pdf::loadView('pdf.admit-card', $data)
            ->setPaper('A4');

        return $pdf->download(
            'AdmitCard_' . $data['student']['admission_no'] . '.pdf'
        );
    }

}
