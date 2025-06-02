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
        return $this->belongsToMany(WorkPhase::class)
                    ->withPivot('pivot_order')  // samo ako ti treba order iz pivot
                    ->withTimestamps();
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
