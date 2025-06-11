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
        'status',
        'status_progresije',
        'created_by',
        'updated_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->generateFullName();
        });

        static::updating(function ($model) {
            $model->generateFullName();
        });
    }

    protected function generateFullName(): void
    {
        $product = $this->product ?? $this->load('product')->product;
        $code = $product?->code ?? 'NO-CODE';

        $this->full_name = "{$this->work_order_number}.{$this->series}.{$code}.{$this->quantity}";
    }

    // Relations

    public function orderRequest()
    {
        return $this->belongsTo(OrderRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The work phases that belong to the WorkOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function workPhases()
    // {
    //     return $this->hasMany(WorkPhase::class);
    // }

    public function items()
    {
        return $this->hasMany(WorkOrderItem::class, 'work_order_id');
    }

    // Additional methods

    // public function completedTimePercentage(): float
    // {
    //     $totalTime = $this->workPhases()->sum('time_norm');
    //     if ($totalTime == 0) {
    //         return 0.0;
    //     }

    //     $completedTime = $this->workPhases()
    //         ->where('is_completed', true) // ili 'end_time', prilagodi po potrebi
    //         ->sum('time_norm');

    //     return round(($completedTime / $totalTime) * 100, 2);
    // }

    public static function getEloquentQuery(): Builder
    {
        return parent::query()->with('items');
    }
    public function updateStatusBasedOnItems(): void
    {
        $items = $this->items()->get(); // <- Fetchuj uvek iz baze

        if ($items->isEmpty()) {
            return;
        }

        if ($items->every(fn ($item) => $item->is_confirmed)) {
            $this->status = 'zavrsen';
        } elseif ($items->contains(fn ($item) => $item->total_completed > 0)) { // ili start_time, ako koristiš
            $this->status = 'u_toku';
        } else {
            $this->status = 'aktivan';
        }

        $this->save();
    }
    // Accessors

    public function getProductCodeAttribute()
    {
        return $this->product?->code;
    }
}
