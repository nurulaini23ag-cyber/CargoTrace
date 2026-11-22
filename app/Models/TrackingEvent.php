<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    use HasFactory;

    // Standard Tracking Event Types
    public const TYPE_CREATED = 'CREATED';
    public const TYPE_BOOKED = 'BOOKED';
    public const TYPE_PICKED_UP = 'PICKED_UP';
    public const TYPE_DEPARTED = 'DEPARTED';
    public const TYPE_IN_TRANSIT = 'IN_TRANSIT';
    public const TYPE_ARRIVED_AT_PORT = 'ARRIVED_AT_PORT';
    public const TYPE_CUSTOMS_HOLD = 'CUSTOMS_HOLD';
    public const TYPE_CUSTOMS_CLEARED = 'CUSTOMS_CLEARED';
    public const TYPE_DELIVERED = 'DELIVERED';
    public const TYPE_EXCEPTION = 'EXCEPTION';
    public const TYPE_CANCELLED = 'CANCELLED';

    public static function allowedEventTypes(): array
    {
        return [
            self::TYPE_CREATED,
            self::TYPE_BOOKED,
            self::TYPE_PICKED_UP,
            self::TYPE_DEPARTED,
            self::TYPE_IN_TRANSIT,
            self::TYPE_ARRIVED_AT_PORT,
            self::TYPE_CUSTOMS_HOLD,
            self::TYPE_CUSTOMS_CLEARED,
            self::TYPE_DELIVERED,
            self::TYPE_EXCEPTION,
            self::TYPE_CANCELLED,
        ];
    }

    protected $fillable = [
        'shipment_id',
        'event_type',
        'status',
        'event_time',
        'location_id',
        'location_name',
        'latitude',
        'longitude',
        'description',
        'is_exception_event',
        'created_by',
    ];

    public static function boot()
    {
        parent::boot();

        // Enforce immutable, append-only tracking events
        static::updating(function () {
            throw new \DomainException("Tracking events are append-only and cannot be modified.");
        });

        static::deleting(function () {
            throw new \DomainException("Tracking events are immutable and cannot be deleted.");
        });
    }

    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_exception_event' => 'boolean',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
