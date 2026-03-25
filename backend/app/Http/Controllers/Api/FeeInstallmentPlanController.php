<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StudentFeeAssignment;
use App\Models\FeeInstallmentPlan;
use App\Models\FeeInstallment;
use App\Models\FeePayment;
use App\Models\FeeStructure;


class FeeInstallmentPlanController extends Controller
{
    public function store(Request $request, StudentFeeAssignment $assignment)
    {
        /* ----------------------------------------
           0. Safety checks
        ---------------------------------------- */

        // Assignment must be active
        if (! $assignment->is_active) {
            return response()->json([
                'message' => 'Inactive fee assignment'
            ], 422);
        }

        /* ----------------------------------------
           1. Validate request
        ---------------------------------------- */
        $data = $request->validate([
            'total_installments' => 'required|integer|min:1|max:12',
        ]);

        /* ----------------------------------------
           2. Prevent duplicate installment plan
        ---------------------------------------- */
        if ($assignment->installment_plan_id) {
            return response()->json([
                'message' => 'Installment plan already exists for this fee'
            ], 409);
        }

        /* ----------------------------------------
           3. BLOCK if payment already started
           (IMPROVEMENT 1)
        ---------------------------------------- */
        $paidAmount = FeePayment::where(
                'student_fee_assignment_id',
                $assignment->id
            )
            ->where('payment_status', 'SUCCESS')
            ->sum('amount');

        if ($paidAmount > 0) {
            return response()->json([
                'message' => 'Cannot create installment plan after payment has started'
            ], 422);
        }

        DB::beginTransaction();

        try {

            /* ----------------------------------------
               4. Create installment plan
            ---------------------------------------- */
            $totalInstallments = $data['total_installments'];
            $totalAmount       = $assignment->total_amount;

            $installmentAmount = round(
                $totalAmount / $totalInstallments,
                2
            );

            $plan = FeeInstallmentPlan::create([
                'student_fee_assignment_id' => $assignment->id,
                'school_id'        => $assignment->school_id,
                'academic_year_id' => $assignment->academic_year_id,
                'class_id'         => $assignment->class_id,
                'name'             => $totalInstallments . ' Installments',
                'total_installments' => $totalInstallments,
                'installment_amount' => $installmentAmount, // ✅ snapshot
                'is_active'        => 1,
            ]);

            // Link plan to assignment
            $assignment->installment_plan_id = $plan->id;
            $assignment->save();

            /* ----------------------------------------
               5. Calculate exact installment amounts
            ---------------------------------------- */
            $baseAmount = floor($totalAmount / $totalInstallments);
            $remainder  = $totalAmount - ($baseAmount * $totalInstallments);

            /* ----------------------------------------
               6. Create installments
            ---------------------------------------- */
            for ($i = 1; $i <= $totalInstallments; $i++) {

                $amount = $baseAmount;

                // Add remainder to last installment
                if ($i === $totalInstallments) {
                    $amount += $remainder;
                }

                FeeInstallment::create([
                    'student_fee_assignment_id' => $assignment->id,
                    'installment_no'            => $i,
                    'amount'                    => $amount,
                    'status'                    => 'PENDING',
                ]);
            }

            DB::commit();

            return response()->json([
                'message'            => 'Installment plan created successfully',
                'plan_id'            => $plan->id,
                'total_installments' => $totalInstallments,
                'installment_amount' => $installmentAmount,
                'total_amount'       => $totalAmount
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create installment plan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function status(FeeStructure $feeStructure)
{
    $assignment = StudentFeeAssignment::where(
        'fee_structure_id',
        $feeStructure->id
    )->first();

    if (!$assignment || !$assignment->installment_plan_id) {
        return response()->json([
            'has_plan' => false
        ]);
    }

    $plan = FeeInstallmentPlan::find(
        $assignment->installment_plan_id
    );

    return response()->json([
        'has_plan' => true,
        'installments' => $plan->total_installments
    ]);
}


}
