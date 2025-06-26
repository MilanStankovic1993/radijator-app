<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'transferred_count',
        'is_confirmed',
    ];

    protected $casts = [
        'total_completed' => 'float',
    ];

    protected $attributes = [
        'total_completed' => 0,
    ];

    protected $appends = ['ready_to_transfer_count'];

    protected static function booted()
    {
        static::saved(function ($item) {
            $item->workOrder?->updateStatusBasedOnItems();
            $item->workOrder?->checkIfFullyTransferredAndUpdate();
        });

        static::deleted(function ($item) {
            $item->workOrder?->updateStatusBasedOnItems();
            $item->workOrder?->checkIfFullyTransferredAndUpdate();
        });
    }
    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function workPhase() { return $this->belongsTo(WorkPhase::class); }
    public function product() { return $this->belongsTo(Product::class); }

    public function getReadyToTransferCountAttribute(): int
    {
        return max(floor($this->total_completed) - $this->transferred_count, 0);
    }
}
