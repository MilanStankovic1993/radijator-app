<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasCommonFeatures;
use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Builder;

class WorkOrder extends Model
{
    use HasUserTracking, HasFactory, HasCommonFeatures;

    protected $fillable = [
        'full_name',
        'user_id',
        'product_id',
        'work_order_number',
        'series',
        'command_in_series',
        'launch_date',
        'quantity',
        'type', // ✅ Dodato
        'status',
        'status_progresije',
        'created_by',
        'updated_by',
    ];

    /**
     * Register model event bindings.
     *
     * This method is called when the model is booted. The model is booted
     * automatically by Eloquent when it's being used. The boot method is a
     * convenient place to register model event bindings.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->generateFullName();
        });

        static::updating(function ($model) {
            $model->generateFullName();
        });
    }

    /**
     * Generates the full name for the work order, based on type.
     *
     * If the type is 'custom', the full name is not generated.
     *
     * Otherwise, the full name is generated in the following format:
     * WorkOrderNumber.ProductName.Series-Quantity
     */
    protected function generateFullName(): void
    {
        if ($this->type === 'custom') {
            return; // ✅ Ne diramo, korisnik unosi ručno
        }

        $product = $this->product ?? $this->load('product')->product;
        $name = $product?->name ?? 'NO-NAME';
        $this->full_name = "{$this->work_order_number}.{$name}.{$this->series}-{$this->quantity}";
    }

    // Relations

    /**
     * Returns the order request which this work order is a part of.
     *
     * @return BelongsTo
     */
    public function orderRequest()
    {
        return $this->belongsTo(OrderRequest::class);
    }

    /**
     * Returns the product which this work order is for.
     *
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Returns the user associated with this work order.
     *
     * @return BelongsTo
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the work order items associated with this work order.
     *
     * Work order items are individual items which are a part of the work order.
     * They are used to track the production of the work order.
     *
     * @return HasMany
     */
    public function items()
    {
        return $this->hasMany(WorkOrderItem::class, 'work_order_id');
    }

    /**
     * Returns the work phase which this work order is a part of.
     *
     * Work phases are individual phases of production which are a part of the
     * production process. Each work order is associated with a single work phase.
     *
     * @return BelongsTo
     */
    public function workPhase()
    {
        return $this->belongsTo(WorkPhase::class);
    }

    /**
     * Get the eloquent query for the work order model.
     *
     * By default, the query will include all work order items.
     *
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::query()->with('items');
    }

    /**
     * Update the status of this work order based on the status of its items.
     *
     * The status of the work order will be set to one of the following:
     *  - 'zavrsen' if all items are confirmed.
     *  - 'u_toku' if any item has a total_completed greater than 0.
     *  - 'aktivan' if none of the above conditions are met.
     */
    public function updateStatusBasedOnItems(): void
    {
        $items = $this->items()->get();

        if ($items->isEmpty()) {
            return;
        }

        if ($items->every(fn ($item) => $item->is_confirmed)) {
            $this->status = 'zavrsen';
        } elseif ($items->contains(fn ($item) => $item->total_completed > 0)) {
            $this->status = 'u_toku';
        } else {
            $this->status = 'aktivan';
        }

        $this->save();
    }
    
    /**
     * Calculate the completion percentage for this work order.
     *
     * This method iterates over all work order items associated with
     * this work order and sums up the total required time and total
     * completed time. The completion percentage is then calculated
     * by dividing the total completed time by the total required time
     * and multiplying by 100.
     *
     * @return float
     */
    public function getCompletionPercentageAttribute(): float
    {
        $totalRequiredTime = 0;
        $totalCompletedTime = 0;

        foreach ($this->items as $item) {
            $workPhase = $item->workPhase;

            if (!$workPhase || $workPhase->time_norm === null) {
                continue;
            }

            $timeNorm = $workPhase->time_norm;
            $required = $item->required_to_complete ?? 0;
            $completed = $item->total_completed ?? 0;

            $totalRequiredTime += $required * $timeNorm;
            $totalCompletedTime += $completed * $timeNorm;
        }

        if ($totalRequiredTime === 0) {
            return 0;
        }

        return round(($totalCompletedTime / $totalRequiredTime) * 100, 2);
    }

    /**
     * Returns the code of the product associated with this work order.
     *
     * @return string|null
     */
    public function getProductCodeAttribute()
    {
        return $this->product?->code;
    }
}
