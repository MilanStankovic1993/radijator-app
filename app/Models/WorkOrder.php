<?php
// app/Models/WorkOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasCommonFeatures;

class WorkOrder extends Model
{
    use HasFactory;
    use HasCommonFeatures;

    protected $fillable = [
        'work_order_number',
        'user_id',
        'launch_date',
        'product_id',
        'status',
        'quantity',
    ];

    public function orderRequest()
    {
        return $this->belongsTo(OrderRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(WorkOrderItem::class);
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('items');
    }

}
