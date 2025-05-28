<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'description',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
