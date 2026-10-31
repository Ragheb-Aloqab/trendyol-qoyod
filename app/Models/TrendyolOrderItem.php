<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrendyolOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'trendyol_order_id',
        'line_item_id',
        'barcode',
        'merchant_sku',
        'product_code',
        'product_name',
        'quantity',
        'unit_price',
        'vat_rate',
        'discount',
        'currency',
        'raw_payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'discount' => 'decimal:2',
            'raw_payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(TrendyolOrder::class, 'trendyol_order_id');
    }
}
