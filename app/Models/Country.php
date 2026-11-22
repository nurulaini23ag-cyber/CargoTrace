<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'region',
        'default_port',
        'latitude',
        'longitude',
        'flag_emoji',
        'is_origin',
        'is_destination',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_origin' => 'boolean',
        'is_destination' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'origin_country_id');
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function buyers(): HasMany
    {
        return $this->hasMany(Buyer::class);
    }

    public function exportTransactions(): HasMany
    {
        return $this->hasMany(TradeTransaction::class, 'origin_country_id');
    }

    public function importTransactions(): HasMany
    {
        return $this->hasMany(TradeTransaction::class, 'destination_country_id');
    }
}
