<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCommonFeatures;
use App\Traits\HasUserTracking;

class Warehouse extends Model
{
    use HasUserTracking;
    use HasCommonFeatures;

    protected $fillable = [
        'product_id',
        'quantity',
        'location',
        'status',
        'created_by',
        'updated_by',
    ];

    // Statusi: na_cekanju, na_stanju, izdato
    public const STATUS_NA_CEKANJU = 'na_cekanju';
    public const STATUS_NA_STANJU   = 'na_stanju';
    public const STATUS_IZDATO      = 'izdato';

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_NA_CEKANJU => 'Na čekanju',
            self::STATUS_NA_STANJU => 'Na stanju',
            self::STATUS_IZDATO => 'Izdato',
        ];
    }
    public function items()
    {
        return $this->hasMany(WarehouseItem::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_NA_CEKANJU;
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_NA_STANJU;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_IZDATO;
    }
}
