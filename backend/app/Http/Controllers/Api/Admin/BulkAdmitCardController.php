<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

class BulkAdmitCardController extends Controller
{
    public function download(Exam $exam, $classId, $sectionId)
    {
        // School isolation
        if ($exam->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        // Fetch students
        $students = Student::where([
            'school_id'  => $exam->school_id,
            'class_id'   => $classId,
            'section_id' => $sectionId,
            'is_active'  => 1,
        ])->get();

        if ($students->isEmpty()) {
            abort(404, 'No students found for this class & section.');
        }

        // Temp zip file
        $zipPath = storage_path(
            'app/temp/admit_cards_exam_' . $exam->id . '.zip'
        );

        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $adminController = app(
            \App\Http\Controllers\Api\Admin\ExamAdmitCardController::class
        );

        foreach ($students as $student) {
            // Reuse existing preview logic
            $preview = $adminController
                ->preview($exam, $student)
                ->getData(true);

            $pdf = Pdf::loadView('pdf.admit-card', $preview)
                ->setPaper('A4')
                ->output();

            $filename = 'AdmitCard_' . $student->admission_no . '.pdf';
            $zip->addFromString($filename, $pdf);
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
