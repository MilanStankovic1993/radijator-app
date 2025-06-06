<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCommonFeatures;
use App\Traits\HasUserTracking;

class Product extends Model
{
    use HasUserTracking;

    use HasCommonFeatures;
    protected $fillable = ['name', 'code', 'description', 'specifications','import_file', 'price', 'status',
        'created_by',
        'updated_by'];

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
