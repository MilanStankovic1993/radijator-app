<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCommonFeatures;
use App\Traits\HasUserTracking;

class Warehouse extends Model
{
    use HasUserTracking;
    use HasCommonFeatures;

    protected $fillable = ['product_id', 'quantity',
        'created_by',
        'updated_by'];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }
}
