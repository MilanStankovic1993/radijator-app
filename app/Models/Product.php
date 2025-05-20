<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function workPhases()
    {
        return $this->hasMany(\App\Models\WorkPhase::class);
    }
}
