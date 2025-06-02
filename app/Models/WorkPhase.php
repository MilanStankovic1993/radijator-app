<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCommonFeatures;

class WorkPhase extends Model
{
    use HasCommonFeatures;

    protected $fillable = ['name', 'location', 'description'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_work_phase')
                    ->withTimestamps();
    }

    public function workOrderItems()
    {
        return $this->hasMany(WorkOrderItem::class);
    }
}
