<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentFeeAssignment;

class StudentFeeSummaryController extends Controller
{
    /**
     * Get complete fee summary of a student
     *
     * Includes:
     * - Total fee
     * - Paid amount
     * - Due amount
     * - Status
     * - Fee head (purpose) breakup
     * - Payment history (receipts)
     */
    public function show(Student $student)
    {
        $assignment = StudentFeeAssignment::with([
                'feeStructure.items.feeHead',
                'payments'
            ])
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        if (! $assignment) {
            return response()->json([
                'message' => 'No fee assignment found for this student'
            ], 404);
        }

        return response()->json([
            'student' => [
                'id'            => $student->id,
                'name'          => $student->name,
                'admission_no'  => $student->admission_no,
                'class_id'      => $student->class_id,
                'section_id'    => $student->section_id,
            ],

            'fee_summary' => [
                'total_fee'   => $assignment->total_amount,
                'paid_amount' => $assignment->paid_amount,
                'due_amount'  => $assignment->due_amount,
                'status'      => $assignment->status,
            ],

            // ✅ Fee head / purpose-wise breakup
            'fee_breakup' => $assignment->feeStructure->items->map(function ($item) {
                return [
                    'fee_head_id'   => $item->fee_head_id,
                    'fee_head_name' => $item->feeHead->name,
                    'amount'        => $item->amount,
                ];
            }),

            // ✅ Payment / receipt history
            'payments' => $assignment->payments->map(function ($payment) {
                return [
                    'receipt_no'   => $payment->receipt_no,
                    'amount'       => $payment->amount,
                    'payment_mode' => $payment->payment_mode,
                    'paid_at'      => $payment->paid_at
                        ? $payment->paid_at->toDateString()
                        : null,
                ];
            }),
        ]);
    }
}

