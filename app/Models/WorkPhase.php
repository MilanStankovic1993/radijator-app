<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkPhase extends Model
{
        // Dodaj ovde sva polja koja možeš masovno dodeljivati (mass assign)
    protected $fillable = [
        'name',
        'description',
    ];
    public function product()
    {
        
        return $this->belongsTo(\App\Models\Product::class);
    }
}
