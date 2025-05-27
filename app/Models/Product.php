<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCommonFeatures;

class Product extends Model
{
    use HasCommonFeatures;
    protected $fillable = ['name', 'code', 'description', 'specifications','import_file', 'price', 'status'];

    public function workPhases()
    {
        return $this->hasMany(WorkPhase::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class);
    }
}
