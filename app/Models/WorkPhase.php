<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCommonFeatures;
use App\Traits\HasUserTracking;

class WorkPhase extends Model
{
    use HasUserTracking;
    use HasCommonFeatures;

    protected $fillable = ['name', 'location', 'time_norm', 'description', 'number_of_workers', 'created_by', 'updated_by'];

    /**
     * The products that belong to the WorkPhase
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_work_phase')
                    ->withTimestamps();
    }

    /**
     * Get the work order items associated with the work phase.
     *
     * This method establishes a one-to-many relationship between
     * the WorkPhase and WorkOrderItem models. Each work phase can
     * have multiple work order items associated with it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workOrderItems()
    {
        return $this->hasMany(WorkOrderItem::class);
    }
}
