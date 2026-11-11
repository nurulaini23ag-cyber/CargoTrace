# CargoTrace Architecture Specification

## 1. Layered Pattern Overview

In compliance with Slide 15 architectural guidelines, CargoTrace strictly separates presentation/HTTP concerns from domain rules using the Service Layer pattern:

```
[ HTTP Requests / Web Browser / REST Client ]
                      │
                      ▼
            [ Routes & Middleware ]
     (auth:sanctum, role:admin,operator,customer)
                      │
                      ▼
         [ Controllers & Form Requests ]
 (StoreShipmentRequest, Api\ShipmentController, Web\ShipmentController)
                      │
                      ▼
              [ Service Layer ]
  ├── ShipmentService   : Status state machine, soft deletes, lifecycle rules
  ├── TrackingService   : Chronological append-only timeline, geocoordinates
  ├── EtaService        : Schedule variance, deterministic threshold matrix
  ├── ExceptionService  : Disruption resolution, ETA impact propagation
  ├── ExternalApiService: Open-Meteo weather client & Nominatim geocoder
  └── AuditService      : Security audit ledger transactions
                      │
                      ▼
             [ Eloquent Models ]
 (Shipment, TrackingEvent, ShipmentException, Carrier, Location, User)
                      │
                      ▼
          [ Persistence Storage ]
   (MySQL 8.0 Production / SQLite Test Environment)
```

## 2. Key Design Decisions

1. **Deterministic State Machine:** Rather than permitting arbitrary status modifications, `ShipmentService` validates transitions through an explicit transition graph. Statuses like `DELIVERED` and `CANCELLED` are terminal.
2. **Append-Only Chronological Tracking:** Tracking events cannot overwrite prior entries. Adding an event requires timestamp validation to prevent retrospective timeline corruption.
3. **External Resilience:** External API calls to Open-Meteo and Nominatim utilize timeout protection (4 seconds) and deterministic fallbacks to guarantee uptime even when remote APIs are unavailable.
