<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Product;
use App\Models\User;

class ProductCreatedNotification extends Notification
{
    use Queueable;

    public Product $product;
    public User $performedBy;

    public function __construct(Product $product, User $performedBy)
    {
        $this->product = $product;
        $this->performedBy = $performedBy;
    }

    // 👇 Ovde definišemo da ide preko baze, NE mail
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // 👇 Ovo je ono što se upisuje u `notifications` tabelu
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => '✅ Novi proizvod je kreiran',
            'message' => "Korisnik {$this->performedBy->name} je kreirao proizvod: {$this->product->name}",
            'product_id' => $this->product->id,
            'performed_by_id' => $this->performedBy->id,
            'performed_by_name' => $this->performedBy->name,
            'url' => url("/admin/products/{$this->product->id}"),
        ];
    }
}
