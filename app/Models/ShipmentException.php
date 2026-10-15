<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentException extends Model
{
    use HasFactory;

    public const TYPE_CUSTOMS_HOLD = 'CUSTOMS_HOLD';
    public const TYPE_WEATHER_DELAY = 'WEATHER_DELAY';
    public const TYPE_PORT_CONGESTION = 'PORT_CONGESTION';
    public const TYPE_DAMAGED = 'DAMAGED';
    public const TYPE_MISROUTED = 'MISROUTED';
    public const TYPE_DOCUMENTATION_ISSUE = 'DOCUMENTATION_ISSUE';

    public const STATUS_OPEN = 'OPEN';
    public const STATUS_INVESTIGATING = 'INVESTIGATING';
    public const STATUS_RESOLVED = 'RESOLVED';

    public const SEVERITY_LOW = 'LOW';
    public const SEVERITY_MEDIUM = 'MEDIUM';
    public const SEVERITY_HIGH = 'HIGH';
    public const SEVERITY_CRITICAL = 'CRITICAL';

    protected $fillable = [
        'shipment_id',
        'exception_type',
        'severity',
        'status',
        'detected_at',
        'resolved_at',
        'impact_hours',
        'description',
        'resolution_notes',
        'logged_by',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'impact_hours' => 'integer',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }
}
