<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentFeeAssignment;
use App\Models\FeeInstallment;
use App\Models\FeeInstallmentPlan;

use App\Models\FeeStructure;
use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentFeeAssignmentController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        // 1️⃣ Resolve school context
        if ($user->school_id) {
            $schoolId = $user->school_id;
        } else {
            $request->validate([
                'school_id' => 'required|exists:schools,id',
            ]);
            $schoolId = $request->school_id;
        }

        // 2️⃣ Validate request
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        // 3️⃣ Academic year lock
        $year = AcademicYear::findOrFail($data['academic_year_id']);
        if ($year->isReadOnly()) {
            return response()->json([
                'message' => 'Academic year is closed. Fees are read-only.'
            ], 403);
        }

        // 4️⃣ Load fee structure with items
        $feeStructure = FeeStructure::with('items')
            ->where('id', $data['fee_structure_id'])
            ->where('school_id', $schoolId)
            ->firstOrFail();

        $totalAmount = $feeStructure->items->sum('amount');

        $assigned = [];
        $skipped  = [];

        DB::transaction(function () use (
            $data,
            $schoolId,
            $totalAmount,
            &$assigned,
            &$skipped
        ) {
            foreach ($data['student_ids'] as $studentId) {

                $student = Student::where('id', $studentId)
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $data['academic_year_id'])
                    ->where('is_active', true)
                    ->first();

                if (! $student) {
                    $skipped[] = [
                        'student_id' => $studentId,
                        'reason' => 'Student not eligible'
                    ];
                    continue;
                }

                // Prevent duplicate assignment
                $exists = StudentFeeAssignment::where([
                    'academic_year_id' => $data['academic_year_id'],
                    'student_id' => $studentId,
                ])->exists();

                if ($exists) {
                    $skipped[] = [
                        'student_id' => $studentId,
                        'reason' => 'Fee already assigned'
                    ];
                    continue;
                }

                StudentFeeAssignment::create([
                    'school_id' => $schoolId,
                    'academic_year_id' => $data['academic_year_id'],
                    'student_id' => $studentId,
                    'fee_structure_id' => $data['fee_structure_id'],
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'due_amount' => $totalAmount,
                    'status' => 'pending',
                ]);

                $assigned[] = $studentId;
            }
        });

        return response()->json([
            'message' => 'Student fee assignment completed',
            'assigned_count' => count($assigned),
            'skipped_count' => count($skipped),
            'skipped' => $skipped,
        ]);
    }
    public function assign(FeeStructure $feeStructure, Request $request)
    {
        if ($feeStructure->is_active != 1) {
            return response()->json([
                'message' => 'Only active fee structures can be assigned.'
            ], 422);
        }

        $request->validate([
            'installment_count' => 'required|integer|min:1|max:12'
        ]);

        $installmentCount = (int) $request->installment_count;

        $feeStructure->load('items');

        if ($feeStructure->items->isEmpty()) {
            return response()->json([
                'message' => 'Fee structure has no fee items.'
            ], 422);
        }

        $totalAmount = $feeStructure->items->sum('amount');

        $schoolId = auth()->user()->school_id ?? 1;

        // Fetch students of class automatically
        $students = Student::where('class_id', $feeStructure->class_id)
            ->where('academic_year_id', $feeStructure->academic_year_id)
            ->where('is_active', 1)
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'message' => 'No students found in this class.'
            ], 404);
        }

        DB::beginTransaction();

        try {

            // Create class-level installment plan (only once)
            $plan = null;

            if ($installmentCount > 1) {
                $plan = FeeInstallmentPlan::firstOrCreate(
                    [
                        'school_id'        => $schoolId,
                        'academic_year_id' => $feeStructure->academic_year_id,
                        'class_id'         => $feeStructure->class_id,
                        'name'             => $installmentCount . ' Installments'
                    ],
                    [
                        'total_installments' => $installmentCount,
                        'is_active'          => 1
                    ]
                );
            }

            foreach ($students as $student) {

                // Prevent duplicate assignment
                $exists = StudentFeeAssignment::where([
                    'student_id' => $student->id,
                    'fee_structure_id' => $feeStructure->id
                ])->exists();

                if ($exists) continue;

                $assignment = StudentFeeAssignment::create([
                    'school_id'        => $schoolId,
                    'student_id'       => $student->id,
                    'academic_year_id' => $feeStructure->academic_year_id,
                    'class_id'         => $feeStructure->class_id,
                    'fee_structure_id' => $feeStructure->id,
                    'assignment_type'  => 'ACADEMIC',
                    'base_amount'      => $totalAmount,
                    'override_amount'  => 0,
                    'total_amount'     => $totalAmount,
                    'paid_amount'      => 0,
                    'due_amount'       => $totalAmount,
                    'status'           => 'UNPAID',
                    'is_active'        => 1,
                    'assigned_at'      => now(),
                    'installment_plan_id' => $plan?->id,
                ]);

                // Create installment rows per student
                if ($installmentCount > 1) {

                    $baseAmount = floor($totalAmount / $installmentCount);
                    $remainder  = $totalAmount - ($baseAmount * $installmentCount);

                    for ($i = 1; $i <= $installmentCount; $i++) {

                        $amount = $baseAmount;

                        if ($i === $installmentCount) {
                            $amount += $remainder;
                        }

                        FeeInstallment::create([
                            'student_fee_assignment_id' => $assignment->id,
                            'installment_no' => $i,
                            'amount'         => $amount,
                            'status'         => 'PENDING',
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message'        => 'Fee structure assigned class-wise successfully.',
                'students_count' => $students->count(),
                'total_amount'   => $totalAmount,
                'installments'   => $installmentCount
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Fee assignment failed.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function assignAdhoc(Request $request)
    {
        $validated = $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'title'      => 'required|string|max:255',
            'amount'     => 'required|numeric|min:1',
            'due_date'   => 'nullable|date',
            'remarks'    => 'nullable|string'
        ]);

        $user = auth()->user();
        $schoolId = $user->school_id;

        // Fetch active students for selected class/section
        $studentsQuery = Student::where('school_id', $schoolId)
            ->where('class_id', $validated['class_id'])
            ->where('is_active', 1);

        if (!empty($validated['section_id'])) {
            $studentsQuery->where('section_id', $validated['section_id']);
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            return response()->json([
                'message' => 'No students found for selected class/section.'
            ], 422);
        }

        DB::beginTransaction();

        try {

            $insertedCount = 0;

            foreach ($students as $student) {

                // Prevent duplicate active ADHOC assignment
                $alreadyExists = StudentFeeAssignment::where('student_id', $student->id)
                    ->where('assignment_type', 'ADHOC')
                    ->where('title', $validated['title'])
                    ->where('academic_year_id', $student->academic_year_id)
                    ->where('is_active', 1)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                StudentFeeAssignment::create([
                    'school_id'        => $schoolId,
                    'student_id'       => $student->id,
                    'academic_year_id' => $student->academic_year_id,
                    'class_id'         => $student->class_id,
                    'fee_structure_id' => null,
                    'installment_plan_id' => null,
                    'assignment_type'  => 'ADHOC',
                    'title'            => $validated['title'],
                    'due_date'         => $validated['due_date'] ?? null,
                    'remarks'          => $validated['remarks'] ?? null,
                    'base_amount'      => $validated['amount'],
                    'override_amount'  => 0,
                    'total_amount'     => $validated['amount'],
                    'status'           => 'UNPAID',
                    'is_active'        => 1,
                    'assigned_at'      => now(),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $insertedCount++;
            }

            DB::commit();

            return response()->json([
                'message' => 'Adhoc fee assigned successfully.',
                'students_affected' => $insertedCount
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to assign adhoc fee.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
