<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeHead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeHeadController extends Controller
{
    public function index(Request $request)
    {
        $query = FeeHead::where('school_id', auth()->user()->school_id);

        if ($request->has('active')) {
            $query->where('is_active', $request->active);
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('fee_heads')->where(function ($q) {
                    return $q->where('school_id', auth()->user()->school_id);
                })
            ],
            'description' => 'nullable|string'
        ]);

        $feeHead = FeeHead::create([
            'school_id'   => auth()->user()->school_id,
            'name'        => $request->name,
            'code'        => strtoupper($request->code),
            'description' => $request->description
        ]);

        return response()->json($feeHead, 201);
    }

    public function toggleStatus(FeeHead $feeHead)
    {
        $this->authorizeFeeHead($feeHead);

        $feeHead->update([
            'is_active' => ! $feeHead->is_active
        ]);

        return response()->json([
            'message' => 'Status updated',
            'is_active' => $feeHead->is_active
        ]);
    }

    private function authorizeFeeHead(FeeHead $feeHead)
    {
        if ($feeHead->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized');
        }
    }
}
