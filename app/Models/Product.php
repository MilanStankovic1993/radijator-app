<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'code', 'description', 'specifications','import_file', 'price', 'status'];

    public function workPhases()
    {
        return $this->hasMany(WorkPhase::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
