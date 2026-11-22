<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    // 8-stage Commodity Shipment Status Workflow
    public const WORKFLOW_ORDER_CONFIRMED = 'Order Confirmed';
    public const WORKFLOW_PROCESSING = 'Processing';
    public const WORKFLOW_READY_FOR_SHIPMENT = 'Ready for Shipment';
    public const WORKFLOW_SHIPPED = 'Shipped';
    public const WORKFLOW_IN_TRANSIT = 'In Transit';
    public const WORKFLOW_CUSTOMS_CLEARANCE = 'Customs Clearance';
    public const WORKFLOW_ARRIVED_AT_DESTINATION = 'Arrived at Destination';
    public const WORKFLOW_DELIVERED = 'Delivered';

    public static function workflowSteps(): array
    {
        return [
            self::WORKFLOW_ORDER_CONFIRMED,
            self::WORKFLOW_PROCESSING,
            self::WORKFLOW_READY_FOR_SHIPMENT,
            self::WORKFLOW_SHIPPED,
            self::WORKFLOW_IN_TRANSIT,
            self::WORKFLOW_CUSTOMS_CLEARANCE,
            self::WORKFLOW_ARRIVED_AT_DESTINATION,
            self::WORKFLOW_DELIVERED,
        ];
    }

    // Retain legacy state constants for backward compatibility
    public const STATUS_CREATED = 'CREATED';
    public const STATUS_BOOKED = 'BOOKED';
    public const STATUS_PICKED_UP = 'PICKED_UP';
    public const STATUS_IN_TRANSIT = 'IN_TRANSIT';
    public const STATUS_DELIVERED = 'DELIVERED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const SEVERITY_NONE = 'NONE';
    public const SEVERITY_LOW = 'LOW';
    public const SEVERITY_MEDIUM = 'MEDIUM';
    public const SEVERITY_HIGH = 'HIGH';

    protected $fillable = [
        'shipment_number',
        'tracking_number',
        'trade_transaction_id',
        'product_id',
        'quantity',
        'unit',
        'exporter_id',
        'importer_id',
        'origin_country_id',
        'destination_country_id',
        'shipping_method',
        'vessel_carrier',
        'departure_port',
        'destination_port',
        'departure_date',
        'current_location_name',
        'current_latitude',
        'current_longitude',
        'container_number',
        'temperature_celsius',
        'humidity_percent',
        'simulated_route',
        // Legacy fields retained for backwards compatibility
        'customer_id',
        'carrier_id',
        'origin_id',
        'destination_id',
        'cargo_type',
        'weight',
        'volume',
        'current_status',
        'expected_departure',
        'expected_arrival',
        'estimated_arrival',
        'actual_departure',
        'actual_arrival',
        'is_delayed',
        'delay_hours',
        'delay_severity',
        'delay_reason',
        'special_instructions',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'volume' => 'float',
            'quantity' => 'float',
            'current_latitude' => 'float',
            'current_longitude' => 'float',
            'temperature_celsius' => 'float',
            'humidity_percent' => 'float',
            'is_delayed' => 'boolean',
            'delay_hours' => 'float',
            'expected_departure' => 'datetime',
            'expected_arrival' => 'datetime',
            'estimated_arrival' => 'datetime',
            'actual_departure' => 'datetime',
            'actual_arrival' => 'datetime',
            'departure_date' => 'datetime',
            'simulated_route' => 'array',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($shipment) {
            $year = date('Y');
            $lastId = static::withTrashed()->max('id') ?? 0;
            $code = 'CTC-' . $year . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            if (empty($shipment->shipment_number)) {
                $shipment->shipment_number = $code;
            }
            if (empty($shipment->tracking_number)) {
                $shipment->tracking_number = $shipment->shipment_number;
            }
        });
    }

    public function tradeTransaction(): BelongsTo
    {
        return $this->belongsTo(TradeTransaction::class, 'trade_transaction_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function exporter(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'exporter_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'importer_id');
    }

    public function originCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    public function shipmentTrackingEvents(): HasMany
    {
        return $this->hasMany(ShipmentTrackingEvent::class)->orderBy('event_time', 'asc');
    }

    // Backward compatible relations
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class, 'shipment_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'shipment_id')->orderBy('event_time', 'asc');
    }

    public function latestTrackingEvent(): HasOne
    {
        return $this->hasOne(TrackingEvent::class, 'shipment_id')->latestOfMany('event_time');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class, 'shipment_id')->latest('detected_at');
    }

    public function latestException(): HasOne
    {
        return $this->hasOne(ShipmentException::class, 'shipment_id')->latestOfMany();
    }

    public function openExceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class, 'shipment_id')
            ->whereIn('status', ['OPEN', 'INVESTIGATING']);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'shipment_id');
    }

    public function isTerminal(): bool
    {
        return in_array($this->current_status, [self::STATUS_DELIVERED, self::STATUS_CANCELLED, self::WORKFLOW_DELIVERED], true);
    }

    public function getActiveTrackingNumber(): string
    {
        return $this->tracking_number ?? $this->shipment_number;
    }

    public function getCommodityTypeAttribute(): string
    {
        $cargo = strtoupper($this->cargo_type ?? '');
        if (str_contains($cargo, 'COCOA') || str_contains($cargo, 'KAKAO')) {
            return 'COCOA';
        }
        return 'COFFEE';
    }

    /**
     * Resolve the exact current geographic position of the cargo.
     * Answers: "di mana barang itu sudah sampai"
     */
    public function getCurrentPosition(): array
    {
        // 1. If delivered, cargo is at destination port
        if ($this->current_status === self::STATUS_DELIVERED || $this->current_status === self::WORKFLOW_DELIVERED) {
            return [
                'latitude' => (float) ($this->destination?->latitude ?? 0),
                'longitude' => (float) ($this->destination?->longitude ?? 0),
                'location_name' => $this->destination?->name ?? 'Destination Port',
                'stage' => 'DELIVERED',
                'is_arrived' => true,
                'last_updated' => $this->actual_arrival?->format('Y-m-d H:i') ?? $this->updated_at->format('Y-m-d H:i'),
                'progress_percent' => 100,
                'event_description' => 'Cargo successfully discharged and received at destination port terminal.',
            ];
        }

        // 2. If newly created or booked, cargo is at origin port waiting for departure
        if (in_array($this->current_status, [self::STATUS_CREATED, self::STATUS_BOOKED, self::WORKFLOW_ORDER_CONFIRMED, self::WORKFLOW_PROCESSING, self::WORKFLOW_READY_FOR_SHIPMENT], true)) {
            return [
                'latitude' => (float) ($this->origin?->latitude ?? 0),
                'longitude' => (float) ($this->origin?->longitude ?? 0),
                'location_name' => $this->origin?->name ?? 'Origin Port',
                'stage' => $this->current_status,
                'is_arrived' => false,
                'last_updated' => $this->expected_departure?->format('Y-m-d H:i') ?? $this->updated_at->format('Y-m-d H:i'),
                'progress_percent' => 10,
                'event_description' => 'Cargo staged at origin CFS warehouse awaiting vessel embarkation.',
            ];
        }

        // 3. If in transit or picked up, check latest tracking event with valid coordinates
        $latestEvent = $this->trackingEvents()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('event_time', 'desc')
            ->first();

        if ($latestEvent) {
            $totalEvents = max(1, $this->trackingEvents()->count());
            $pct = min(90, max(25, (int) round(($totalEvents / ($totalEvents + 1)) * 100)));

            return [
                'latitude' => (float) $latestEvent->latitude,
                'longitude' => (float) $latestEvent->longitude,
                'location_name' => $latestEvent->location_name ?? $this->current_location_name ?? 'In Transit Sea Corridor',
                'stage' => $this->current_status,
                'is_arrived' => false,
                'last_updated' => $latestEvent->event_time?->format('Y-m-d H:i') ?? $this->updated_at->format('Y-m-d H:i'),
                'progress_percent' => $pct,
                'event_description' => $latestEvent->description ?? 'Vessel underway along maritime corridor.',
            ];
        }

        // 4. Fallback to shipment table direct coordinates or midpoint
        if ($this->current_latitude && $this->current_longitude) {
            return [
                'latitude' => (float) $this->current_latitude,
                'longitude' => (float) $this->current_longitude,
                'location_name' => $this->current_location_name ?? 'In Transit Sea Route',
                'stage' => $this->current_status,
                'is_arrived' => false,
                'last_updated' => $this->updated_at->format('Y-m-d H:i'),
                'progress_percent' => 50,
                'event_description' => 'Container in transit along international shipping lane.',
            ];
        }

        // 5. Midpoint interpolation between origin and destination
        $lat = ($this->origin?->latitude ?? 0) * 0.5 + ($this->destination?->latitude ?? 0) * 0.5;
        $lon = ($this->origin?->longitude ?? 0) * 0.5 + ($this->destination?->longitude ?? 0) * 0.5;
        return [
            'latitude' => round($lat, 4),
            'longitude' => round($lon, 4),
            'location_name' => 'High Seas International Transit',
            'stage' => $this->current_status,
            'is_arrived' => false,
            'last_updated' => $this->updated_at->format('Y-m-d H:i'),
            'progress_percent' => 50,
            'event_description' => 'Vessel cruising international maritime corridor.',
        ];
    }

    public function getWorkflowProgressPercentage(): int
    {
        $steps = self::workflowSteps();
        $currentIndex = array_search($this->current_status, $steps);
        if ($currentIndex === false) {
            return 15;
        }
        return (int) round((($currentIndex + 1) / count($steps)) * 100);
    }
}
