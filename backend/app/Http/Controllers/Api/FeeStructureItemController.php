<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\FeeStructureItem;
use App\Models\FeeHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeStructureItemController extends Controller
{
    public function store(Request $request, FeeStructure $feeStructure)
    {
        // Only draft structures can be modified
        if ($feeStructure->is_active) {
            return response()->json([
                'message' => 'Cannot modify an active fee structure.'
            ], 422);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.fee_head_id' => ['required', 'exists:fee_heads,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $feeStructure) {

            foreach ($validated['items'] as $item) {

                // Prevent duplicate fee head per structure
                $exists = FeeStructureItem::where('fee_structure_id', $feeStructure->id)
                    ->where('fee_head_id', $item['fee_head_id'])
                    ->exists();

                if ($exists) {
                    throw new \Exception('Duplicate fee head detected.');
                }

                FeeStructureItem::create([
                    'fee_structure_id' => $feeStructure->id,
                    'fee_head_id' => $item['fee_head_id'],
                    'amount' => $item['amount'],
                ]);
            }
        });

        return response()->json([
            'message' => 'Fee structure items added successfully.'
        ], 201);
    }
    public function index(FeeStructure $feeStructure)
    {
        return $feeStructure
            ->items()
            ->whereNull('deleted_at')
            ->get();
    }
    




}
