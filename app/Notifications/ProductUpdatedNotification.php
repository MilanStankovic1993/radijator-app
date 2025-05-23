<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Product;
use App\Models\User;

class ProductUpdatedNotification extends Notification
{
    use Queueable;

    public Product $product;
    public User $performedBy;

    public function __construct(Product $product, User $performedBy)
    {
        $this->product = $product;
        $this->performedBy = $performedBy;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => '✏️ Proizvod je izmenjen',
            'message' => "Korisnik {$this->performedBy->name} je izmenio proizvod: {$this->product->name}",
            'product_id' => $this->product->id,
            'performed_by_id' => $this->performedBy->id,
            'performed_by_name' => $this->performedBy->name,
            'url' => url("/admin/products/{$this->product->id}"),
        ];
    }
}
