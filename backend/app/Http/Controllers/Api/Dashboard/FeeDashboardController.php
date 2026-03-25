<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use Carbon\Carbon;

class FeeDashboardController extends Controller
{
    public function academicYearSummary(AcademicYear $academicYear)
    {
        // Students count
        $totalStudents = Student::where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->count();

        // Fee assignment aggregates
        $feeTotals = StudentFeeAssignment::where('academic_year_id', $academicYear->id)
            ->selectRaw('
                SUM(total_amount) as total_fee,
                SUM(paid_amount) as total_collected,
                SUM(due_amount) as total_due
            ')
            ->first();

        // Student payment status
        $paidStudents = StudentFeeAssignment::where('academic_year_id', $academicYear->id)
            ->where('status', 'paid')
            ->count();

        $pendingStudents = StudentFeeAssignment::where('academic_year_id', $academicYear->id)
            ->where('status', '!=', 'paid')
            ->count();

        // Today collection
        $todayCollection = FeePayment::where('academic_year_id', $academicYear->id)
            ->whereDate('paid_at', Carbon::today())
            ->sum('amount');

        // Monthly collection
        $monthlyCollection = FeePayment::where('academic_year_id', $academicYear->id)
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->sum('amount');

        return response()->json([
            'academic_year' => [
                'id' => $academicYear->id,
                'name' => $academicYear->name,
            ],

            'students' => [
                'total' => $totalStudents,
                'paid' => $paidStudents,
                'pending' => $pendingStudents,
            ],

            'fees' => [
                'total_assigned' => (float) ($feeTotals->total_fee ?? 0),
                'total_collected' => (float) ($feeTotals->total_collected ?? 0),
                'total_due' => (float) ($feeTotals->total_due ?? 0),
            ],

            'collections' => [
                'today' => (float) $todayCollection,
                'this_month' => (float) $monthlyCollection,
            ],
        ]);
    }
}
