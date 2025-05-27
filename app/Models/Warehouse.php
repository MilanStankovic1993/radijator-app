<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCommonFeatures;

class Warehouse extends Model
{
    use HasCommonFeatures;
    protected $fillable = ['product_id', 'quantity'];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }
}
