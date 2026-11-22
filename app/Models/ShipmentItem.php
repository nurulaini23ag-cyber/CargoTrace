<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentItem extends Model
{
    use HasFactory;

    public const CATEGORY_COCOA = 'COCOA';
    public const CATEGORY_COFFEE = 'COFFEE';

    protected $fillable = [
        'shipment_id',
        'product_category',
        'product_name',
        'variety',
        'grade',
        'item_description',
        'hs_code',
        'quantity',
        'package_type',
        'package_count',
        'net_weight',
        'weight_kg',
        'volume_cbm',
        'moisture_content',
        'temperature_target',
        'is_hazardous',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'package_count' => 'integer',
            'net_weight' => 'float',
            'weight_kg' => 'float',
            'volume_cbm' => 'float',
            'moisture_content' => 'float',
            'temperature_target' => 'float',
            'is_hazardous' => 'boolean',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function isCocoa(): bool
    {
        return strtoupper($this->product_category) === self::CATEGORY_COCOA;
    }

    public function isCoffee(): bool
    {
        return strtoupper($this->product_category) === self::CATEGORY_COFFEE;
    }
}
