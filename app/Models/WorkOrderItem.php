<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasCommonFeatures;
use App\Traits\HasUserTracking;

class WorkOrderItem extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'work_order_id',
        'work_phase_id',
        'product_id',
        'required_to_complete',
        'total_completed',
        'is_confirmed',
    ];
    protected $casts = [
        'total_completed' => 'float',
    ];

    protected $attributes = [
        'total_completed' => 0,
    ];

    protected static function booted()
    {
        static::saved(function ($item) {
            $item->workOrder?->updateStatusBasedOnItems();
            $item->workOrder?->recalculateTransferCounts(); // <-- poziva update polja
        });

        static::deleted(function ($item) {
            $item->workOrder?->updateStatusBasedOnItems();
            $item->workOrder?->recalculateTransferCounts(); // <-- poziva update polja
        });
    }
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

