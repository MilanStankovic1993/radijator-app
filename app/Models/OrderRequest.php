<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCommonFeatures;
use App\Traits\HasUserTracking;

class OrderRequest extends Model
{
    use HasUserTracking;

    use HasCommonFeatures;
    protected $fillable = [
        'order_code',
        'customer_id',
        'customer_name', 
        'status',
        'expected_delivery_date',
        'created_by',
        'updated_by'
    ];
    protected $casts = [
        'expected_delivery_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderRequestItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}