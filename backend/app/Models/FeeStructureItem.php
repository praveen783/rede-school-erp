<?php

namespace App\Models;
use App\Models\FeeHead;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructureItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fee_structure_id',
        'fee_head_id',
        'amount',
    ];
    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }
    public function feeHead()
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }
}
