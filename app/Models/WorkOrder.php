<?php
// app/Models/WorkOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasCommonFeatures;
use App\Traits\HasUserTracking;

class WorkOrder extends Model
{
    use HasUserTracking;
    use HasFactory;
    use HasCommonFeatures;

    protected $fillable = [
        'full_name',
        'user_id',
        'product_id',
        'work_order_number',
        'product_code',
        'series',
        'command_in_series',
        'launch_date',
        'quantity',
        'status',
        'created_by',
        'updated_by',
    ];
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->full_name = $model->work_order_number . '-' . $model->product_code . '-' . $model->series . '-' . $model->quantity;
        });
    }
    public function orderRequest()
    {
        return $this->belongsTo(OrderRequest::class);
    }

    public function confirmedItemsPercentage(): float
    {
        $total = $this->items()->count();

        if ($total === 0) {
            return 0;
        }

        $confirmed = $this->items()->where('is_confirmed', 1)->count();

        return round(($confirmed / $total) * 100, 2);
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
        return $this->hasMany(\App\Models\WorkOrderItem::class, 'work_order_id');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('items');
    }

}
