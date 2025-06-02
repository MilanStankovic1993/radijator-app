<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasCommonFeatures;

class WorkOrderItem extends Model
{
    protected $fillable = [
        'work_order_id',
        'work_phase_id',
        'product_id',
        'code',
        'status',
        'is_confirmed',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function workPhase()
    {
        return $this->belongsTo(WorkPhase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

