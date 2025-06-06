<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUserTracking;

class Service extends Model
{
    use HasFactory;
    use HasUserTracking;


    protected $fillable = [
        'customer_id',
        'description',
        'created_by',
        'updated_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
