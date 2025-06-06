<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUserTracking;

class Customer extends Model
{
    use HasUserTracking;

    use HasFactory;

    // Polja koja mogu masovno da se popunjavaju (mass assignment)
    protected $fillable = [
        'name',
        'type',
        // Fizicka lica
        'jmbg',
        'phone',
        'email',
        'address',
        'city',
        'date_of_birth',
        'id_card_number',
        'note',
        // Kompanije
        'company_name',
        'pib',
        'contact_person',
        'created_by',
        'updated_by',
    ];

    // Date casting (ako želiš da 'date_of_birth' bude Carbon objekat)
    protected $dates = [
        'date_of_birth',
        'created_at',
        'updated_at',
    ];

    // Eventualno možeš dodati pristupe ili helper metode za tip kupca, npr:
    public function isIndividual(): bool
    {
        return $this->type === 'individual';
    }

    public function isCompany(): bool
    {
        return $this->type === 'company';
    }

    public function orderRequests()
    {
        return $this->hasMany(OrderRequest::class);
    }

    public function service()
    {
        return $this->hasMany(Service::class);
    }
}
