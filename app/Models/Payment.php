<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const METHOD_VA = 'VIRTUAL_ACCOUNT';
    public const METHOD_CC = 'CREDIT_CARD';
    public const METHOD_LC = 'LETTER_OF_CREDIT';
    public const METHOD_QRIS = 'QRIS';
    public const METHOD_BANK_TRANSFER = 'BANK_TRANSFER';

    protected $fillable = [
        'payment_number',
        'trade_transaction_id',
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'payment_channel',
        'payment_status',
        'virtual_account_number',
        'card_brand',
        'card_last_four',
        'transaction_reference',
        'paid_at',
        'expires_at',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'gateway_response' => 'array',
    ];

    public function tradeTransaction(): BelongsTo
    {
        return $this->belongsTo(TradeTransaction::class, 'trade_transaction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSuccess(): bool
    {
        return $this->payment_status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->payment_status === self::STATUS_PENDING;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->payment_number)) {
                $lastId = static::max('id') ?? 0;
                $model->payment_number = 'PAY-2026-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
            }
            if (empty($model->transaction_reference)) {
                $model->transaction_reference = 'CT-GW-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
            }
            if (empty($model->expires_at)) {
                $model->expires_at = now()->addHours(24);
            }
        });
    }
}
