# CargoTrace REST API Specification (Slides 12 & 13)

Base URL: `http://localhost:8000/api`

## Authentication
CargoTrace utilizes Laravel Sanctum Bearer tokens. Include `Authorization: Bearer <token>` and `Accept: application/json` in all protected requests.

### Endpoints Overview

| Method | Endpoint | Role Allowed | Description |
|---|---|---|---|
| POST | `/api/register` | Public | Register customer account |
| POST | `/api/login` | Public | Authenticate user & issue Sanctum token |
| POST | `/api/logout` | Authenticated | Revoke current access token |
| GET | `/api/user` | Authenticated | Retrieve authenticated user profile |
| GET | `/api/shipments` | Authenticated | List shipments (scoped to customer if role=customer) |
| POST | `/api/shipments` | Admin, Operator | Create shipment and items manifest |
| GET | `/api/shipments/{id}` | Authenticated | Retrieve shipment by ID |
| PUT | `/api/shipments/{id}` | Admin, Operator | Update shipment parameters |
| DELETE | `/api/shipments/{id}` | Admin | Soft-delete shipment |
| GET | `/api/shipments/{id}/tracking` | Authenticated | Retrieve chronological tracking timeline |
| POST | `/api/shipments/{id}/tracking` | Admin, Operator | Append verified tracking event |
| GET | `/api/shipments/{id}/exceptions` | Authenticated | List exceptions for shipment |
| POST | `/api/shipments/{id}/exceptions` | Admin, Operator | Log exception and adjust ETA |
| PATCH | `/api/exceptions/{id}` | Admin, Operator | Resolve exception with notes |
| GET | `/api/carriers` | Authenticated | List active logistics carriers |
| POST | `/api/carriers` | Admin | Register new carrier |
| GET | `/api/locations` | Authenticated | List ports and logistics hubs |
| POST | `/api/locations` | Admin | Register new port or inland hub |
| GET | `/api/external/weather` | Authenticated | Live marine weather at coordinates |
| GET | `/api/external/geocode` | Authenticated | Nominatim geocoding search |
