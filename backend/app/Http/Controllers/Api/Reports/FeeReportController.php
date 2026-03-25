<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\StudentFeeAssignment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeReportController extends Controller
{
   
    public function academicYearSummary($academicYearId)
    {
        $user = auth()->user();

        // Validate academic year
        $year = AcademicYear::findOrFail($academicYearId);

        // Scope by school + academic year
        $query = StudentFeeAssignment::where('school_id', $user->school_id)
            ->where('academic_year_id', $academicYearId);

        $totalFee = $query->sum('total_amount');
        $totalPaid = $query->sum('paid_amount');
        $totalDue  = $query->sum('due_amount');

        $collectionPercentage = $totalFee > 0
            ? round(($totalPaid / $totalFee) * 100, 2)
            : 0;

        return response()->json([
            'academic_year' => [
                'id' => $year->id,
                'name' => $year->name,
            ],
            'summary' => [
                'total_fee' => $totalFee,
                'total_collected' => $totalPaid,
                'total_due' => $totalDue,
                'collection_percentage' => $collectionPercentage,
            ]
        ]);
    }
    public function classWiseSummary($academicYearId)
    {
        $user = auth()->user();

        $data = DB::table('student_fee_assignments')
            ->join('students', 'student_fee_assignments.student_id', '=', 'students.id')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->where('student_fee_assignments.school_id', $user->school_id)
            ->where('student_fee_assignments.academic_year_id', $academicYearId)
            ->groupBy('classes.id', 'classes.name')
            ->select(
                'classes.id as class_id',
                'classes.name as class_name',
                DB::raw('SUM(student_fee_assignments.total_amount) as total_fee'),
                DB::raw('SUM(student_fee_assignments.paid_amount) as total_collected'),
                DB::raw('SUM(student_fee_assignments.due_amount) as total_due')
            )
            ->get()
            ->map(function ($row) {
                $row->collection_percentage = $row->total_fee > 0
                    ? round(($row->total_collected / $row->total_fee) * 100, 2)
                    : 0;
                return $row;
            });

        return response()->json([
            'academic_year_id' => (int) $academicYearId,
            'classes' => $data
        ]);
    }
    public function feeHeadWiseReport(Request $request, $academicYearId)
    {
        $query = DB::table('fee_structure_items')
            ->join('fee_heads', 'fee_structure_items.fee_head_id', '=', 'fee_heads.id')
            ->join('fee_structures', 'fee_structure_items.fee_structure_id', '=', 'fee_structures.id')
            ->join('student_fee_assignments', 'student_fee_assignments.fee_structure_id', '=', 'fee_structures.id')
            ->leftJoin('fee_payments', 'fee_payments.student_fee_assignment_id', '=', 'student_fee_assignments.id')
            ->where('student_fee_assignments.academic_year_id', $academicYearId);

        if ($request->filled('class_id')) {
            $query->where('fee_structures.class_id', $request->class_id);
        }

        $rows = $query
            ->select(
                'fee_heads.id as fee_head_id',
                'fee_heads.name as fee_head_name',
                DB::raw('SUM(fee_structure_items.amount) as total_assigned'),
                DB::raw('SUM(fee_payments.amount) as total_collected')
            )
            ->groupBy('fee_heads.id', 'fee_heads.name')
            ->get();

        return response()->json([
            'academic_year_id' => (int) $academicYearId,
            'fee_heads' => $rows->map(fn ($r) => [
                'fee_head_id' => $r->fee_head_id,
                'fee_head_name' => $r->fee_head_name,
                'total_assigned' => (float) $r->total_assigned,
                'total_collected' => (float) ($r->total_collected ?? 0),
                'total_due' => (float) ($r->total_assigned - ($r->total_collected ?? 0)),
            ]),
        ]);
    }

}
