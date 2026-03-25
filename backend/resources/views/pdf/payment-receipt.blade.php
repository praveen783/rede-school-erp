<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Payment Receipt</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #000; padding: 8px; }
    </style>
</head>
<body>

<div class="header">
    <h2>{{ $schoolName }}</h2>
    <h4>Fee Payment Receipt</h4>
</div>

<table>
    <tr>
        <th>Student Name</th>
        <td>{{ $student->name }}</td>
    </tr>
    
    <tr>
        <th>Admission No</th>
        <td>{{ $student->admission_no }}</td>
    </tr>

    <tr>
        <th>Class</th>
        <td>{{ $student->class->name ?? '-' }}</td>
    </tr>
    <tr>
        <th>Academic Year</th>
        <td>{{ $academicYear }}</td>
    </tr>

    <tr>
        <th>Fee Name</th>
        <td>{{ $feeName }}</td>
    </tr>
    
    <tr>
        <th>Installment</th>
        <td>{{ $installmentNo ?? 'Full Payment' }}</td>
    </tr>
    <tr>
        <th>Amount Paid</th>
        <td>₹ {{ number_format($payment->amount, 2) }}</td>
    </tr>
    <tr>
        <th>Payment ID</th>
        <td>{{ $payment->razorpay_payment_id }}</td>
    </tr>
    <tr>
        <th>Order ID</th>
        <td>{{ $payment->razorpay_order_id }}</td>
    </tr>
    <tr>
        <th>Date</th>
        <td>{{ $payment->paid_on }}</td>
    </tr>
</table>

</body>
</html>