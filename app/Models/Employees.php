<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class employees extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'jmbg',
        'phone',
        'email',
        'address',
        'city',
        'date_of_birth',
        'id_card_number',
        'created_by',
        'updated_by',
        'import_file'
    ];

    protected $dates = [
        'date_of_birth',
        'created_at',
        'updated_at',
    ];

}
