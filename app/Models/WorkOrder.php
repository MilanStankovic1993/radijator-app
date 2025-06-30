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
        'type',
        'status',
        'status_progresije',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['transferred_count', 'ready_to_transfer_count'];

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
        if ($this->type === 'custom') {
            return;
        }

        $product = $this->product ?? $this->load('product')->product;
        $name = $product?->name ?? 'NO-NAME';
        $this->full_name = "{$this->work_order_number}.{$name}.{$this->series}.{$this->quantity}";
    }

    public function orderRequest() { return $this->belongsTo(OrderRequest::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(WorkOrderItem::class, 'work_order_id'); }
    public function workPhase() { return $this->belongsTo(WorkPhase::class); }

    public static function getEloquentQuery(): Builder
    {
        return parent::query()->with('items');
    }

    public function updateStatusBasedOnItems(): void
    {
        $items = $this->items()->get();

        if ($items->isEmpty()) return;

        if ($items->every(fn ($item) => $item->is_confirmed)) {
            $this->status = 'zavrsen';
        } elseif ($items->contains(fn ($item) => $item->total_completed > 0)) {
            $this->status = 'u_toku';
        } else {
            $this->status = 'aktivan';
        }

        $this->save();
    }

    public function getTransferredCountAttribute(): int
    {
        return \App\Models\WarehouseItem::where('work_order_id', $this->id)
            ->whereIn('status', ['na_cekanju', 'aktivno'])
            ->count();
    }

    public function getReadyToTransferCountAttribute(): int
    {
        if ($this->items->isEmpty()) {
            return 0;
        }

        // Minimalan broj završenih po fazama (koliko celih proizvoda je spremno)
        $minCompleted = $this->items
            ->map(fn ($item) => floor($item->total_completed))
            ->min();

        // Minimalan broj već transferovanih po fazama (koliko puta je već ceo proizvod poslat)
        $minTransferred = $this->items
            ->map(fn ($item) => $item->transferred_count)
            ->min();

        return max(0, $minCompleted - $minTransferred);
    }

    public function checkIfFullyTransferredAndUpdate(): void
    {
        $transferred = $this->getTransferredCountAttribute(); // koliko je otišlo u magacin
        $ready = $this->getReadyToTransferCountAttribute();   // koliko još čeka

        // Ako ništa više ne čeka i sve je prebačeno
        if ($transferred >= $this->quantity && $ready === 0) {
            $this->is_transferred_to_warehouse = true;
            $this->status = 'zavrsen';
            $this->save();
        }
    }

    public function getCompletionPercentageAttribute(): float
    {
        $totalRequiredTime = 0;
        $totalCompletedTime = 0;

        foreach ($this->items as $item) {
            $workPhase = $item->workPhase;
            if (!$workPhase || $workPhase->time_norm === null) continue;

            $required = $item->required_to_complete ?? 0;
            $completed = $item->total_completed ?? 0;
            $totalRequiredTime += $required * $workPhase->time_norm;
            $totalCompletedTime += $completed * $workPhase->time_norm;
        }

        return $totalRequiredTime === 0 ? 0 : round(($totalCompletedTime / $totalRequiredTime) * 100, 2);
    }

    public function getProductCodeAttribute() { return $this->product?->code; }
    public function isTransferredToWarehouse(): bool { return $this->is_transferred_to_warehouse; }
}
