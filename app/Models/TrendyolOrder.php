<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TrendyolOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'shipment_package_id',
        'packet_number',
        'status',
        'currency',
        'gross_amount',
        'total_discount',
        'total_price',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'tax_number',
        'tax_office',
        'is_commercial',
        'invoice_address',
        'shipment_address',
        'raw_payload',
        'order_date',
        'package_last_modified_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'total_price' => 'decimal:2',
            'is_commercial' => 'boolean',
            'invoice_address' => 'array',
            'shipment_address' => 'array',
            'raw_payload' => 'array',
            'order_date' => 'datetime',
            'package_last_modified_date' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(TrendyolOrderItem::class);
    }

    public function syncs(): HasMany
    {
        return $this->hasMany(QoyodSync::class);
    }

    public function invoiceSync(): HasOne
    {
        return $this->hasOne(QoyodSync::class)->where('type', QoyodSync::TYPE_INVOICE);
    }

    public function creditNoteSync(): HasOne
    {
        return $this->hasOne(QoyodSync::class)->where('type', QoyodSync::TYPE_CREDIT_NOTE);
    }

    public function getCustomerFullNameAttribute(): string
    {
        return trim(($this->customer_first_name ?? '') . ' ' . ($this->customer_last_name ?? ''));
    }

    public function isApprovedForSync(): bool
    {
        // 1. منع أي طلب أقدم من تاريخ بدء المزامنة المعتمد (مثل قبل 2026-10-01)
        $startDate = AppSetting::get('sync_start_date', config('services.trendyol.sync_start_date', '2026-10-01'));
        if ($this->order_date && ! empty($startDate)) {
            $parsedStart = \Illuminate\Support\Carbon::parse($startDate)->startOfDay();
            if ($this->order_date->lt($parsedStart)) {
                return false;
            }
        }

        // 2. التحقق من أن حالة الطلب معتمدة للمزامنة (افتراضياً: Delivered فقط)
        $approvedStatuses = AppSetting::get('approved_statuses', config('services.trendyol.approved_statuses', ['Delivered']));
        return in_array($this->status, (array) $approvedStatuses, true);
    }
}
