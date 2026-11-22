<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TradeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'transaction_type',
        'exporter_id',
        'importer_id',
        'product_id',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'currency',
        'origin_country_id',
        'destination_country_id',
        'departure_port',
        'destination_port',
        'transaction_date',
        'estimated_arrival_date',
        'incoterms',
        'transaction_status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
        'total_price' => 'float',
        'transaction_date' => 'date',
        'estimated_arrival_date' => 'date',
    ];

    public function exporter(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'exporter_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'importer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function originCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class, 'trade_transaction_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->transaction_id)) {
                $lastId = static::max('id') ?? 0;
                $model->transaction_id = 'TR-2026-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            }
            if (empty($model->total_price) && $model->quantity && $model->unit_price) {
                $model->total_price = $model->quantity * $model->unit_price;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty(['quantity', 'unit_price'])) {
                $model->total_price = $model->quantity * $model->unit_price;
            }
        });
    }
}
