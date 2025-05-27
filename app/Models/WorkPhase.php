<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCommonFeatures;

class WorkPhase extends Model
{
    use HasCommonFeatures;
    protected $fillable = ['name', 'description', 'product_id', 'is_completed'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}
