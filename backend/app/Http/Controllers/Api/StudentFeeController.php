<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use App\Models\AcademicYear;
class StudentFeeController extends Controller
{
    /**
     * GET /student/fees
     * Get logged-in student's fee details
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Get student
        $student = Student::where('user_id', $user->id)
            ->where('is_active', 1)
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not linked with user'
            ], 403);
        }

        $assignments = StudentFeeAssignment::with([
                // 'student.name',
                'student.class',
                'student.section',
                'academicYear',
                'feeStructure.items.feeHead'
            ])
            ->where('student_id', $student->id)
            ->where('is_active', 1)
            ->orderByDesc('assigned_at')
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json([
                'student_id' => $student->id,
                'fees' => []
            ]);
        }

        $fees = $assignments->map(function ($assignment) {

            /* -----------------------------
            ACADEMIC FEE
            ------------------------------*/
            if ($assignment->assignment_type === 'ACADEMIC'
                && $assignment->feeStructure) {

                $items = $assignment->feeStructure->items->map(function ($item) {
                    return [
                        'fee_head' => $item->feeHead->name,
                        'amount' => (float)$item->amount
                    ];
                });

                $title =
                    optional($assignment->student->class)->name
                    . " Annual Fee";
            }

            /* -----------------------------
            ADHOC FEE
            ------------------------------*/
            else {

                $items = [
                    [
                        'fee_head' => $assignment->title,
                        'amount' => (float)$assignment->total_amount
                    ]
                ];

                $title = $assignment->title;
            }

            return [

                'assignment_id' => $assignment->id,

                'assignment_type' => $assignment->assignment_type,

                'title' => $title,

                'class' => optional($assignment->student->class)->name,

                'section' => optional($assignment->student->section)->name,

                'academic_year' =>
                    optional($assignment->academicYear)->name,

                'total_amount' =>
                    (float)$assignment->total_amount,

                'due_amount' =>
                    (float)$assignment->total_amount,

                'status' => $assignment->status,

                'items' => $items
            ];
        });

        return response()->json([
            'student_id' => $student->id,
            'fees' => $fees
        ]);
    }    

    public function show(StudentFeeAssignment $assignment)
    {
        $user = auth()->user();

        // Security check
        if ($assignment->student->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Load relations
        $assignment->load([
            'student.class',
            'student.section',
            'academicYear',
            'feeStructure.items.feeHead',
            'installments'
        ]);

        /*
        |--------------------------------------------------------------------------
        | TITLE FIX FOR BOTH TYPES
        |--------------------------------------------------------------------------
        */

        if ($assignment->assignment_type === 'ACADEMIC') {

            $title =
                optional($assignment->student->class)->name
                . " Annual Fee";

            $items = $assignment->feeStructure->items->map(function ($item) {

                return [
                    'fee_head' => $item->feeHead->name ?? 'Fee',
                    'amount'   => $item->amount
                ];
            });

        }
        else {

            $title = $assignment->title;

            $items = [
                [
                    'fee_head' => $assignment->title,
                    'amount'   => $assignment->total_amount
                ]
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | INSTALLMENTS
        |--------------------------------------------------------------------------
        */

        $installments = $assignment->installments
            ->map(function ($inst) {

                return [
                    'id' => $inst->id,
                    'installment_no' => $inst->installment_no,
                    'due_date' => $inst->due_date,
                    'amount' => $inst->amount,
                    'status' => $inst->status
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | FINAL RESPONSE (FRONTEND FORMAT)
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'assignment_id' => $assignment->id,

            'assignment_type' => $assignment->assignment_type,

            'title' => $title,

            'class' => optional($assignment->student->class)->name,
            
            'section' => optional($assignment->student->section)->name, 

            'academic_year' => optional($assignment->academicYear)->name,

            'total_amount' => $assignment->total_amount,

            'status' => $assignment->status,

            'due_amount' => $assignment->total_amount,

            'items' => $items,

            'installments' => $installments

        ]);
    }
    public function paymentHistory(Request $request)
    {
        $user = auth()->user();

        $payments = FeePayment::with([
            'assignment.feeStructure'
        ])
        ->whereHas('assignment.student', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'SUCCESS')
        ->orderByDesc('created_at')
        ->get();

        return response()->json([
            'payments' => $payments
        ]);
    }
    public function previousFees()
    {
        $user = auth()->user();

        $student = Student::where('user_id', $user->id)->firstOrFail();

        // Get latest academic year for this student
        $latestAssignment = StudentFeeAssignment::where('student_id', $student->id)
            ->orderByDesc('academic_year_id')
            ->first();

        if (!$latestAssignment) {
            return response()->json([
                'previous_fees' => []
            ]);
        }

        $currentYearId = $latestAssignment->academic_year_id;

        $assignments = StudentFeeAssignment::with([
                'academicYear',
                'class',
                'feeStructure'
            ])
            ->where('student_id', $student->id)
            ->where('academic_year_id', '!=', $currentYearId)
            ->orderByDesc('academic_year_id')
            ->get();

        $data = $assignments->map(function ($assignment) {
            return [
                'assignment_id'   => $assignment->id,
                'academic_year'   => $assignment->academicYear->name ?? '-',
                'class_name'      => $assignment->class->name ?? '-',
                'fee_name'        => $assignment->feeStructure->name ?? '-',
                'total_amount'    => $assignment->total_amount,
                'paid_amount'     => $assignment->paid_amount,
                'due_amount'      => $assignment->due_amount,
                'status'          => $assignment->status,
            ];
        });

        return response()->json([
            'previous_fees' => $data
        ]);
    }

    public function previousFeeDetails($id)
    {
        $user = auth()->user();

        $student = Student::where('user_id', $user->id)->firstOrFail();

        $assignment = StudentFeeAssignment::with([
                'academicYear',
                'class',
                'feeStructure',
                'installments',
                'payments'
            ])
            ->where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        return response()->json([
            'assignment' => [
                'id' => $assignment->id,
                'academic_year' => $assignment->academicYear->name ?? '-',
                'class_name' => $assignment->class->name ?? '-',
                'fee_name' => $assignment->feeStructure->name ?? '-',
                'total_amount' => $assignment->total_amount,
                'paid_amount' => $assignment->paid_amount,
                'due_amount' => $assignment->due_amount,
                'status' => $assignment->status,
            ],
            'installments' => $assignment->installments,
            'payments' => $assignment->payments
        ]);
    }
}
