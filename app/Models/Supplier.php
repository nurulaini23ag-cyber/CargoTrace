<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_person',
        'country_id',
        'email',
        'phone',
        'address',
        'products_supplied',
        'certifications',
        'rating',
        'status',
        'user_id',
    ];

    protected $casts = [
        'rating' => 'float',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TradeTransaction::class, 'exporter_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'exporter_id');
    }
}
