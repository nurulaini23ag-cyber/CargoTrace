# CargoTrace Performance & Caching Strategy

## 1. Database Indexing
The database schema utilizes targeted single and composite indexes to ensure instant response times under heavy tracking throughput:
- `shipments.shipment_number`: Unique index for \(O(1)\) reference lookup.
- `shipments.current_status`: Indexed for instant filtering on active state machine phases (`IN_TRANSIT`, `DELIVERED`).
- `shipments.is_delayed`: Indexed for high-priority operational dashboard alerts.
- `tracking_events.event_time`: B-tree index enabling chronological timeline sorting without full table scans.
- `tracking_events.shipment_id`: Foreign key index for swift relationship hydration.
- `audit_logs.created_at`: Indexed for rapid chronological auditing.

## 2. Query Optimization & Eager Loading
To resolve \(N+1\) query issues in views and APIs, controllers consistently eager load relationships:
```php
Shipment::with([
    'customer:id,name,email,company_name',
    'carrier:id,code,name,mode',
    'origin:id,code,name,city,country',
    'destination:id,code,name,city,country'
])->paginate(10);
```

## 3. Remote Service Caching
External network calls to Open-Meteo and OpenStreetMap Nominatim are protected by caching:
* **Marine Weather Data:** Cached for 10 minutes (`Cache::remember('weather_...', 600)`), preventing excessive third-party traffic while keeping environmental context fresh.
* **Geocoding Lookups:** Cached for 1 hour (`Cache::remember('geocode_...', 3600)`), drastically accelerating port coordinate resolution.
* **Fallback Circuit Breaker:** 4-second hard timeout prevents upstream latency spikes from locking user interface requests.
