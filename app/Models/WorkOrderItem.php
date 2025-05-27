<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasCommonFeatures;

class WorkOrderItem extends Model
{
    use HasCommonFeatures;
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'code',
        'name',
        'status',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }
}
