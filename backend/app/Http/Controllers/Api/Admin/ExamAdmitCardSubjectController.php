<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAdmitCard;
use App\Models\ExamAdmitCardSubject;
use Illuminate\Http\Request;

class ExamAdmitCardSubjectController extends Controller
{
    /**
     * Add / Update Subject Dates
     */
    public function store(Request $request, ExamAdmitCard $admitCard)
    {
        if ($admitCard->status === 'Published') {
            return response()->json([
                'message' => 'Cannot modify subjects of a published admit card'
            ], 403);
        }

        $request->validate([
            'subjects'               => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|integer',
            'subjects.*.exam_date'  => 'required|date',
        ]);

        foreach ($request->subjects as $subject) {
            ExamAdmitCardSubject::updateOrCreate(
                [
                    'exam_admit_card_id' => $admitCard->id,
                    'subject_id'         => $subject['subject_id'],
                ],
                [
                    'exam_date' => $subject['exam_date'],
                ]
            );
        }

        return response()->json([
            'message' => 'Subject dates saved successfully'
        ]);
    }
}
