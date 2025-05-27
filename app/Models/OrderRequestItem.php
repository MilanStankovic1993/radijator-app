<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCommonFeatures;

class OrderRequestItem extends Model
{
    use HasCommonFeatures;
    protected $fillable = ['order_request_id', 'product_id', 'quantity'];

    public function orderRequest(): BelongsTo
    {
        return $this->belongsTo(OrderRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
