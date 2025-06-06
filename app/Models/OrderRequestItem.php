<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasCommonFeatures;
use App\Traits\HasUserTracking;

class OrderRequestItem extends Model
{
    use HasUserTracking;

    use HasCommonFeatures;
    protected $fillable = ['order_request_id', 'product_id', 'quantity',
        'created_by',
        'updated_by'];

    public function orderRequest(): BelongsTo
    {
        return $this->belongsTo(OrderRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
