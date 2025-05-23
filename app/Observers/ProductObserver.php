<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductCreatedNotification;
use App\Notifications\ProductUpdatedNotification;
use Illuminate\Support\Facades\Notification;

class ProductObserver
{
    public function created(Product $product): void
    {
        $performedBy = auth()->user();

        // Notifikacije šaljemo samo ako je korisnik ulogovan
        if ($performedBy instanceof User) {
            $users = User::all(); // ili filtrirano po roli: User::where('role', 'admin')->get();
            Notification::send($users, new ProductCreatedNotification($product, $performedBy));
        }
    }

    public function updated(Product $product): void
    {
        $performedBy = auth()->user();

        if ($performedBy instanceof User) {
            $users = User::all();
            Notification::send($users, new ProductUpdatedNotification($product, $performedBy));
        }
    }
}
