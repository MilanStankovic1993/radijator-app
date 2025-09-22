<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'notes',
        'starts_at',
        'ends_at',
        'all_day',
        'email_to',
        'email_at',
        'emailed_at',
        'pre_email_enabled',
        'pre_email_offset_minutes',
        'pre_emailed_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'email_at'  => 'datetime',
        'emailed_at'=> 'datetime',
        'pre_emailed_at' => 'datetime',
        'all_day'   => 'boolean',
        'pre_email_enabled' => 'boolean',
        'pre_email_offset_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
