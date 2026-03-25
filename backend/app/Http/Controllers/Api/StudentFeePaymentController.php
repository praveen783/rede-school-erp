<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use App\Models\FeeInstallment;

class StudentFeePaymentController extends Controller
{
    public function pay(Request $request, $assignmentId)
    {
        $user = $request->user();

        /* --------------------------------
           1. Resolve student from user
        -------------------------------- */
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not linked'
            ], 403);
        }

        /* --------------------------------
           2. Validate request
        -------------------------------- */
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_mode' => [
                'required',
                Rule::in(['CASH','UPI','CARD','BANK_TRANSFER','CHEQUE'])
            ],
            'paid_by' => [
                'required',
                Rule::in(['STUDENT','PARENT','ADMIN'])
            ],
            'payment_gateway' => [
                'nullable',
                Rule::in(['RAZORPAY','STRIPE'])
            ],
            'transaction_ref' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string']
        ]);

        /* --------------------------------
           3. Fetch assignment
        -------------------------------- */
        $assignment = StudentFeeAssignment::where('id', $assignmentId)
            ->where('student_id', $student->id)
            ->where('is_active', 1)
            ->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'Invalid fee assignment'
            ], 404);
        }

        /* --------------------------------
           4. Check if installment plan exists
        -------------------------------- */
        $installments = FeeInstallment::where(
                'student_fee_assignment_id',
                $assignment->id
            )
            ->orderBy('installment_no')
            ->get();

        $hasInstallments = $installments->isNotEmpty();

        /* --------------------------------
           5. Calculate paid so far
        -------------------------------- */
        $paidSoFar = FeePayment::where(
                'student_fee_assignment_id',
                $assignment->id
            )
            ->where('payment_status', 'SUCCESS')
            ->sum('amount');

        $dueAmount = $assignment->total_amount - $paidSoFar;

        /* ======================================================
           INSTALLMENT LOGIC (IF PLAN EXISTS)
        ====================================================== */
        $nextInstallment = null;

        if ($hasInstallments) {

            $nextInstallment = $installments
                ->where('status', 'PENDING')
                ->first();

            if (!$nextInstallment) {
                return response()->json([
                    'message' => 'All installments already paid'
                ], 422);
            }

            // Student must pay exact installment amount
            if ((float)$validated['amount'] !== (float)$nextInstallment->amount) {
                return response()->json([
                    'message' => 'You can only pay next pending installment amount: ₹'
                                . $nextInstallment->amount
                ], 422);
            }
        }
        /* ======================================================
           NORMAL FLEXIBLE PAYMENT (NO PLAN)
        ====================================================== */
        else {
            if ($validated['amount'] > $dueAmount) {
                return response()->json([
                    'message' => 'Payment amount exceeds due amount'
                ], 422);
            }
        }

        /* --------------------------------
           6. Determine payment status
        -------------------------------- */
        $paymentStatus = in_array(
            $validated['payment_mode'],
            ['CASH','CHEQUE','BANK_TRANSFER']
        ) ? 'SUCCESS' : 'PENDING';

        DB::beginTransaction();

        try {

            /* --------------------------------
               7. Create payment
            -------------------------------- */
            $payment = FeePayment::create([
                'school_id'                 => $assignment->school_id,
                'student_fee_assignment_id' => $assignment->id,
                'amount'                    => $validated['amount'],
                'payment_status'            => $paymentStatus,
                'payment_mode'              => $validated['payment_mode'],
                'paid_by'                   => $validated['paid_by'],
                'payment_gateway'           => $validated['payment_gateway'] ?? null,
                'transaction_ref'           => $validated['transaction_ref'] ?? null,
                'paid_at'                   => Carbon::now(),
                'collected_by'              => $validated['paid_by'] === 'ADMIN'
                                                ? $user->id
                                                : null,
                'remarks'                   => $validated['remarks'] ?? null,
            ]);

            /* --------------------------------
               8. If SUCCESS → update installment + assignment
            -------------------------------- */
            if ($paymentStatus === 'SUCCESS') {

                if ($hasInstallments) {

                    // Mark installment as paid
                    $nextInstallment->status  = 'PAID';
                    $nextInstallment->paid_at = now();
                    $nextInstallment->save();

                    // Check remaining installments
                    $remaining = FeeInstallment::where(
                            'student_fee_assignment_id',
                            $assignment->id
                        )
                        ->where('status', 'PENDING')
                        ->count();

                    $assignment->status = $remaining == 0
                        ? 'PAID'
                        : 'PARTIAL';

                } else {

                    $newPaid = $paidSoFar + $validated['amount'];

                    $assignment->status = $newPaid >= $assignment->total_amount
                        ? 'PAID'
                        : 'PARTIAL';
                }

                $assignment->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment recorded successfully',
                'payment' => $payment,
                'summary' => [
                    'total' => $assignment->total_amount,
                    'paid'  => $paidSoFar + $validated['amount'],
                    'due'   => max($dueAmount - $validated['amount'], 0)
                ]
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Payment failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
