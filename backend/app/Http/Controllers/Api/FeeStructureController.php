<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\StudentFee;

class FeeStructureController extends Controller
{
    
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id'         => ['required', 'exists:classes,id'],
            'name'             => ['nullable', 'string', 'max:255'],
        ]);

        // Prevent duplicate fee structure per class + year
        $exists = FeeStructure::where('school_id', $user->school_id)
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('class_id', $validated['class_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Fee structure already exists for this class and academic year.'
            ], 422);
        }

        $feeStructure = FeeStructure::create([
            'school_id'        => $user->school_id,
            'academic_year_id' => $validated['academic_year_id'],
            'class_id'         => $validated['class_id'],
            'name'             => $validated['name'],
            'is_active'        => 0, // draft state
        ]);

        return response()->json($feeStructure, 201);
    }
    public function activate(FeeStructure $feeStructure)
    {
        $user = auth()->user();

        //  School isolation
        if ($feeStructure->school_id !== $user->school_id) {
            return response()->json([
                'message' => 'Unauthorized access to fee structure.'
            ], 403);
        }

        // Already active
        if ($feeStructure->is_active) {
            return response()->json([
                'message' => 'Fee structure is already active.'
            ], 422);
        }

        // No items added
        if ($feeStructure->items()->count() === 0) {
            return response()->json([
                'message' => 'Cannot activate fee structure without fee items.'
            ], 422);
        }

        DB::transaction(function () use ($feeStructure) {

            //  Deactivate any existing active structure
            FeeStructure::where('school_id', $feeStructure->school_id)
                ->where('academic_year_id', $feeStructure->academic_year_id)
                ->where('class_id', $feeStructure->class_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Activate this structure
            $feeStructure->update([
                'is_active' => true
            ]);
        });

        return response()->json([
            'message' => 'Fee structure activated successfully.'
        ]);
    }

    public function index()
    {
        $user = auth()->user();

        $structures = FeeStructure::with([
                'class:id,name',
                'academicYear:id,name'
            ])
            ->where('school_id', $user->school_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($structures);
    }
    public function show(FeeStructure $feeStructure)
    {
        $feeStructure->load([
            'academicYear:id,name',
            'class:id,name',
            'items.feeHead:id,name'
        ]);

        return response()->json($feeStructure);
    }

    public function assignmentStatus(FeeStructure $feeStructure)
    {
        $count = \App\Models\StudentFeeAssignment::where(
            'fee_structure_id',
            $feeStructure->id
        )->count();

        $plan = \App\Models\FeeInstallmentPlan::where([
            'school_id' => $feeStructure->school_id,
            'academic_year_id' => $feeStructure->academic_year_id,
            'class_id' => $feeStructure->class_id
        ])->first();

        return response()->json([
            'is_assigned' => $count > 0,
            'students_count' => $count,
            'installments' => $plan->total_installments ?? 1
        ]);
    }

      
}
