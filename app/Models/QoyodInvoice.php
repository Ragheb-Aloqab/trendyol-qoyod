<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QoyodInvoice extends Model
{
    use HasFactory;

    public const FETCH_STATUS_SUCCESS = 'success';
    public const FETCH_STATUS_PENDING = 'pending';
    public const FETCH_STATUS_FAILED = 'failed';

    protected $fillable = [
        'trendyol_order_id',
        'qoyod_sync_id',
        'qoyod_invoice_id',
        'qoyod_invoice_number',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_discount',
        'total',
        'status',
        'pdf_url',
        'pdf_path',
        'fetch_status',
        'fetch_error',
        'fetched_at',
        'raw_data',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'total' => 'decimal:2',
            'fetched_at' => 'datetime',
            'raw_data' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(TrendyolOrder::class, 'trendyol_order_id');
    }

    public function sync(): BelongsTo
    {
        return $this->belongsTo(QoyodSync::class, 'qoyod_sync_id');
    }
}
