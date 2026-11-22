<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_ADMINISTRATOR = 'administrator';
    public const ROLE_EXPORTER = 'exporter';
    public const ROLE_IMPORTER = 'importer';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_OPERATOR = 'operator';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'company_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_ADMINISTRATOR]);
    }

    public function isExporter(): bool
    {
        return $this->role === self::ROLE_EXPORTER;
    }

    public function isImporter(): bool
    {
        return $this->role === self::ROLE_IMPORTER;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isOperator(): bool
    {
        return $this->role === self::ROLE_OPERATOR;
    }

    public function canManageCommodities(): bool
    {
        return $this->isAdmin() || $this->isExporter() || $this->isOperator();
    }

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class);
    }

    public function buyer(): HasOne
    {
        return $this->hasOne(Buyer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TradeTransaction::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'customer_id');
    }

    public function trackingEventsCreated(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'created_by');
    }

    public function exceptionsLogged(): HasMany
    {
        return $this->hasMany(ShipmentException::class, 'logged_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id')->latest();
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}
