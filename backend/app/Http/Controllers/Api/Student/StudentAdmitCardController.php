<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAdmitCard;
use App\Models\ExamSchedule;
use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentAdmitCardController extends Controller
{
    public function pdf(Exam $exam)
    {
        $user = auth()->user();

        if (!$user->student || !$user->student->is_active) {
            abort(403, 'Student not eligible.');
        }

        $student = $user->student;

        $admitCard = ExamAdmitCard::where([
            'exam_id'    => $exam->id,
            'class_id'   => $student->class_id,
            'section_id' => $student->section_id,
            'status'     => 'Published',
        ])->firstOrFail();

        $schedules = ExamSchedule::with('subject')
            ->where('exam_id', $exam->id)
            ->where('school_id', $student->school_id)
            ->where('academic_year_id', $student->academic_year_id)
            ->orderBy('exam_date')
            ->get();

        \Log::info('Exam schedules loaded', [
            'exam_id' => $exam->id,
            'school_id' => $student->school_id,
            'academic_year_id' => $student->academic_year_id,
            'count' => $schedules->count()
        ]);



        $school = School::findOrFail($student->school_id);

        // ✅ DomPDF-safe absolute photo path
        $studentPhotoPath = null;
        if ($student->photo_path) {
            $abs = public_path($student->photo_path);
            if (file_exists($abs)) {
                $studentPhotoPath = $abs;
            }
        }

        $data = [
            'exam'          => $exam,
            'student'       => $student,
            'school'        => $school,
            'schedules'     => $schedules,
            'admitCard'     => $admitCard,
            'hallTicketNo'  => 'HT-' . $exam->id . '-' . $student->id,
            'studentPhoto'  => $studentPhotoPath,
        ];

        return Pdf::loadView('pdf.admit-card', $data)
            ->setPaper('A4')
            ->download('AdmitCard_' . $student->admission_no . '.pdf');
    }

    public function list()
    {
        $user = Auth::user();

        // 1️ Resolve student
        if (!$user->student || !$user->student->is_active) {
            abort(403, 'Student profile not found or inactive.');
        }

        $student = $user->student;

        // 2️ Fetch PUBLISHED admit cards for this student (class + section)
        $admitCards = ExamAdmitCard::with('exam:id,name')
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('status', 'Published')
            ->orderBy('created_at', 'desc')
            ->get();

        // 3️ Transform response
        $data = $admitCards->map(function ($card) use ($student) {

            return [
                'exam_id'        => $card->exam_id,
                'exam_name'      => $card->exam->name,
                'duration'       => $card->duration_minutes . ' mins',
                'exam_session'   => $card->exam_session,
                'hall_ticket_no' => 'HT-' .
                                    currentAcademicYearId() . '-' .
                                    $card->exam_id . '-' .
                                    $student->id,
                'status'         => $card->status,
                'can_download'   => true
            ];
        });

        return response()->json([
            'student' => [
                'id'   => $student->id,
                'name' => $student->name
            ],
            'admit_cards' => $data
        ]);
    }
    
}
