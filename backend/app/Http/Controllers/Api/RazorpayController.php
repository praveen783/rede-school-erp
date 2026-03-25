<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\StudentFeeAssignment;
use App\Models\FeeInstallment;
use App\Models\FeePayment;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
class RazorpayController extends Controller
{
    /* =========================================================
       CREATE RAZORPAY ORDER
    ==========================================================*/
    public function createOrder(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:student_fee_assignments,id',
            'installment_id' => 'nullable|exists:fee_installments,id'
        ]);

        $user = auth()->user();
        $assignment = StudentFeeAssignment::findOrFail($request->assignment_id);

        // Security check
        if ($assignment->student->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {

            $amount = 0;
            $installmentNo = null;

            /* ---------------- INSTALLMENT PAYMENT ---------------- */
            if ($request->installment_id) {

                $installment = FeeInstallment::findOrFail($request->installment_id);

                if ($installment->status === 'PAID') {
                    return response()->json([
                        'message' => 'Installment already paid'
                    ], 422);
                }

                $amount = $installment->amount;
                $installmentNo = $installment->installment_no;
            }
            /* ---------------- FULL PAYMENT ---------------- */
            else {

                // Calculate actual due amount safely
                $dueAmount = $assignment->due_amount;

                // fallback if due_amount was not initialized properly
                if ($dueAmount <= 0) {
                    $dueAmount = $assignment->total_amount - $assignment->paid_amount;
                }

                if ($dueAmount <= 0) {
                    return response()->json([
                        'message' => 'Nothing due'
                    ], 422);
                }

                $amount = $dueAmount;
            }

            $api = new Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $order = $api->order->create([
                'receipt' => 'fee_' . $assignment->id . '_' . time(),
                'amount' => $amount * 100,
                'currency' => 'INR'
            ]);

            FeePayment::create([
                'school_id' => $assignment->school_id,
                'student_fee_assignment_id' => $assignment->id,
                'installment_no' => $installmentNo,
                'amount' => $amount,
                'gateway' => 'RAZORPAY',
                'status' => 'CREATED',
                'razorpay_order_id' => $order->id,
                'collected_by' => 0
            ]);

            DB::commit();

            return response()->json([
                'order_id' => $order['id'],
                'razorpay_key' => config('services.razorpay.key'),
                'amount' => $amount
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* =========================================================
       VERIFY PAYMENT (Frontend Callback)
    ==========================================================*/
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required'
        ]);

        DB::beginTransaction();

        try {

            $api = new Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            // Signature verification
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ]);

            $payment = FeePayment::where(
                'razorpay_order_id',
                $request->razorpay_order_id
            )->lockForUpdate()->firstOrFail();

            // Prevent duplicate success
            if ($payment->status === 'SUCCESS') {
                DB::commit();
                return response()->json(['message' => 'Already processed']);
            }

            // Fetch actual payment details from Razorpay
            $razorpayPayment = $api->payment->fetch($request->razorpay_payment_id);

            $method = strtolower($razorpayPayment->method);

            // // Map Razorpay methods to your ENUM values
            // switch ($method) {
            //     case 'upi':
            //         $paymentMode = 'UPI';
            //         break;

            //     case 'card':
            //         $paymentMode = 'CARD';
            //         break;

            //     case 'netbanking':
            //         $paymentMode = 'BANK_TRANSFER';
            //         break;

            //     default:
            //         $paymentMode = 'UPI'; // fallback
            //         break;
            // }

            // Fetch actual payment details from Razorpay
            $razorpayPayment = $api->payment->fetch($request->razorpay_payment_id);

            $method = strtolower($razorpayPayment->method);

            // Map Razorpay method to your ENUM values
            switch ($method) {
                case 'upi':
                    $paymentMode = 'UPI';
                    break;

                case 'card':
                    $paymentMode = 'CARD';
                    break;

                case 'netbanking':
                    $paymentMode = 'BANK_TRANSFER';
                    break;

                default:
                    $paymentMode = 'UPI'; // safe fallback
                    break;
            }

            $payment->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'status' => 'SUCCESS',
                'paid_on' => now(),
                'payment_mode' => $paymentMode
            ]);

            // 🔥 Centralized logic
            $this->handleSuccessfulPayment($payment);

            DB::commit();

            return response()->json(['message' => 'Payment verified']);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /* =========================================================
       WEBHOOK HANDLER
    ==========================================================*/
    public function webhook(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret');

        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        try {

            $api = new Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $api->utility->verifyWebhookSignature(
                $payload,
                $signature,
                $webhookSecret
            );

            $event = json_decode($payload);

            if ($event->event === 'payment.captured') {

                $razorpayOrderId =
                    $event->payload->payment->entity->order_id;

                $payment = FeePayment::where(
                    'razorpay_order_id',
                    $razorpayOrderId
                )->first();

                if ($payment && $payment->status !== 'SUCCESS') {

                    DB::transaction(function () use ($payment, $event) {

                        $payment->update([
                            'status' => 'SUCCESS',
                            'razorpay_payment_id' =>
                                $event->payload->payment->entity->id,
                            'paid_on' => now(),
                            'payment_mode' => 'UPI'
                        ]);

                        $this->handleSuccessfulPayment($payment);
                    });
                }
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Invalid signature'
            ], 400);
        }
    }

    /* =========================================================
       CENTRAL SUCCESS HANDLER (NO DUPLICATION)
    ==========================================================*/
    private function handleSuccessfulPayment($payment)
    {
        $assignment = StudentFeeAssignment::lockForUpdate()
            ->with([
                'student.class',
                'student.school',
                'feeStructure' 
            ])
            ->findOrFail($payment->student_fee_assignment_id);

        /* ---------- UPDATE INSTALLMENT ---------- */
        if ($payment->installment_no) {

            $installment = FeeInstallment::where([
                'student_fee_assignment_id' => $assignment->id,
                'installment_no' => $payment->installment_no
            ])->first();

            if ($installment && $installment->status !== 'PAID') {
                $installment->update([
                    'status' => 'PAID',
                    'paid_at' => now()
                ]);
            }
        }

        /* ---------- UPDATE ASSIGNMENT TOTALS ---------- */
        $assignment->paid_amount += $payment->amount;
        $assignment->due_amount -= $payment->amount;

        if ($assignment->due_amount <= 0) {
            $assignment->due_amount = 0;
            $assignment->status = 'PAID';
        } else {
            $assignment->status = 'PARTIAL';
        }

        $assignment->save();

        /* =====================================================
        🔥 GENERATE RECEIPT (IF NOT EXISTS)
        ======================================================*/
        $payment->refresh();

        if (empty($payment->receipt_path)) {

            $student = $assignment->student;
            $schoolName = $student->school->name ?? 'School';
            $academicYear = optional($assignment->academicYear)->name ?? '-';
            $installmentNo = $payment->installment_no;
            
            $feeName = $assignment->feeStructure->name ?? 'Fee';

            $pdf = Pdf::loadView('pdf.payment-receipt', [
                'payment' => $payment,
                'student' => $student,
                'schoolName' => $schoolName,
                'academicYear' => $academicYear,
                'installmentNo' => $installmentNo,
                'feeName' => $feeName
            ]);

            $filename = 'receipt_' . $payment->id . '_' . time() . '.pdf';

            Storage::disk('public')->put(
                'receipts/' . $filename,
                $pdf->output()
            );

            $payment->update([
                'receipt_path' => 'receipts/' . $filename
            ]);
        }
    }
}