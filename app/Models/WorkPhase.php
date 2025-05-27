<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkPhase extends Model
{
    protected $fillable = ['name', 'description', 'product_id', 'is_completed'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}
