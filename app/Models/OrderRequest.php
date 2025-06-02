<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasCommonFeatures;

class OrderRequest extends Model
{
    use HasCommonFeatures;
    protected $fillable = ['customer_id', 'customer_name', 'status'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderRequestItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}