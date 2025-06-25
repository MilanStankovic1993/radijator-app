<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'creator_id',
        'due_date',
    ];

    // Korisnici kojima je dodeljen zadatak
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_read', 'is_done')
            ->withTimestamps();
    }
    public function updateStatus(): void
    {
        $this->loadMissing('users');

        if ($this->users->every(fn ($user) => $user->pivot->is_done)) {
            $this->status = 'zavrsen';
        } elseif ($this->users->contains(fn ($user) => $user->pivot->is_read)) {
            $this->status = 'u radu';
        } else {
            $this->status = 'aktivan';
        }

        $this->save();
    }

    // Korisnik koji je kreirao zadatak
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
