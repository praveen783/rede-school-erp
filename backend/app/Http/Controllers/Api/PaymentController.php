<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function downloadReceipt($id)
    {
        $payment = FeePayment::findOrFail($id);

        // Security check – ensure student owns this payment
        if ($payment->assignment->student->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if (!$payment->receipt_path) {
            abort(404, 'Receipt not found');
        }

        return response()->download(
            storage_path('app/public/' . $payment->receipt_path)
        );
    }
}