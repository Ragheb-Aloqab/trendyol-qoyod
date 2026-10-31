<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QoyodSync extends Model
{
    use HasFactory;

    public const TYPE_INVOICE = 'invoice';
    public const TYPE_CREDIT_NOTE = 'credit_note';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'trendyol_order_id',
        'type',
        'qoyod_id',
        'qoyod_reference',
        'claim_id',
        'status',
        'attempts',
        'last_error',
        'synced_at',
        'request_payload',
        'response_payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'synced_at' => 'datetime',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(TrendyolOrder::class, 'trendyol_order_id');
    }

    public function markAsSuccess(string $qoyodId, ?array $responsePayload = null): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'qoyod_id' => $qoyodId,
            'synced_at' => now(),
            'last_error' => null,
            'response_payload' => $responsePayload,
        ]);
    }

    public function markAsFailed(string $error, ?array $responsePayload = null): void
    {
        $this->increment('attempts');
        $this->update([
            'status' => self::STATUS_FAILED,
            'last_error' => $error,
            'response_payload' => $responsePayload,
        ]);
    }
}
