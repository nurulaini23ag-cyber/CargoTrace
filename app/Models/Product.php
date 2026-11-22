<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'origin_country_id',
        'grade',
        'weight_per_unit',
        'unit',
        'price',
        'currency',
        'stock_availability',
        'product_status',
        'description',
        'specifications',
        'product_image',
        'is_featured',
    ];

    protected $casts = [
        'weight_per_unit' => 'float',
        'price' => 'float',
        'stock_availability' => 'float',
        'is_featured' => 'boolean',
        'specifications' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function originCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TradeTransaction::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2) . ' / ' . $this->unit;
    }
}
