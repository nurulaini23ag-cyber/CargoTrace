# CargoTrace: Global Shipment Visibility & Tracking Platform

[![Web Programming 2 - UAS Final Project](https://img.shields.io/badge/Course-Web%20Programming%202-blue.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B%20%7C%208.3%2B-indigo.svg)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12%2B-red.svg)](https://laravel.com)
[![REST API](https://img.shields.io/badge/REST%20API-Sanctum%20Authenticated-emerald.svg)](https://laravel.com/docs/sanctum)
[![License](https://img.shields.io/badge/License-MIT-gray.svg)](LICENSE)

> **Web Programming 2 • UAS Final Project**  
> Built as an enterprise-grade software engineering portfolio project demonstrating architectural separation of concerns, deterministic business rules, geographic mapping, external API integration, automated testing, and role-based access control.

---

## 1. Problem & Solution Overview

### The Business Problem
Global supply chains suffer from fragmented communication and scattered tracking data:
* **Dispersed Visibility:** Cargo coordinates and milestone updates are siloed across disconnected carrier portals and shipping line manifests.
* **Customer Uncertainty:** Consignees and customers lack real-time visibility into historical movement milestones and accurate estimated arrival (ETA) predictions.
* **Hidden Bottlenecks:** Delays, port congestion, and customs holds remain invisible until critical delivery deadlines have already lapsed.
* **Operational Blind Spots:** Logistics dispatchers lack centralized operational dashboards to prioritize incidents and mitigate supply chain friction.

### The CargoTrace Solution
CargoTrace provides a centralized visibility platform designed for logistics operators and their clients:
* **Shipment Lifecycle State Machine:** Enforces strict backend validation (`CREATED` &rarr; `BOOKED` &rarr; `PICKED_UP` &rarr; `IN_TRANSIT` &rarr; `DELIVERED`). Terminal states (`DELIVERED`, `CANCELLED`) cannot be illegally rolled back.
* **Append-Only Chronological Tracking:** Preserves a complete chain of custody with timestamp sequence validation and checkpoint geocoordinates.
* **Deterministic ETA & Delay Engine:** Evaluates dynamic ETAs against expected schedules, categorizing delays into `LOW` (0–6h), `MEDIUM` (6–24h), and `HIGH` (>24h) with configurable threshold matrices.
* **Active Exception Incident Management:** Tracks operational exceptions (`CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE`) with severity levels, impact hours, and resolution audit trails.
* **Interactive Cartography & Live Weather:** Leaflet.js and OpenStreetMap visualize full origin-to-destination routes with popups, supplemented by real-time marine weather context from the Open-Meteo API.
* **Multi-Role RBAC:** Server-side authorization separating Administrator, Logistics Operator, and Customer privileges.

---

## 2. System Architecture & Engineering Design

CargoTrace strictly separates HTTP delivery from core business logic using a Service Layer architecture pattern as mandated in Slide 15:

```
Browser / REST Client
         │
         ▼
Routes & Middleware (auth, role:admin,operator,customer)
         │
         ▼
Controllers (Web & REST API JSON)
         │
         ▼
Service Layer (Business Rules & Transactions)
  ├── ShipmentService     (Lifecycle transitions, state validation, CRUD)
  ├── TrackingService     (Chronological append-only timeline, geocoordinates)
  ├── EtaService          (Deterministic delay calculation & threshold categorization)
  ├── ExceptionService    (Incident management, delay impact propagation)
  ├── ExternalApiService  (Open-Meteo weather & Nominatim geocoding)
  └── AuditService        (Security audit trail ledger)
         │
         ▼
Data Persistence (Eloquent ORM & Migrations: MySQL 8+ / SQLite)
```

---

## 3. Data Model & Database Schema

The database consists of 10 structured relational tables fulfilling all data model requirements (Slide 11):

```
┌──────────────┐       1:N       ┌──────────────┐
│    users     │────────────────<│ notifications│
└──────┬───────┘                 └──────────────┘
       │ 1:N (customer_id)
       │
       ▼
┌──────────────┐       1:N       ┌──────────────┐
│  shipments   │────────────────<│shipment_items│
└──────┬───────┘                 └──────────────┘
       │ 1:N
       ├────────────────────────<┌──────────────────────┐
       │                         │   tracking_events    │
       │ 1:N                     └──────────────────────┘
       ├────────────────────────<┌──────────────────────┐
       │                         │ shipment_exceptions  │
       │ N:1 (carrier_id)        └──────────────────────┘
       ├────────────────────────>┌──────────────┐
       │                         │   carriers   │
       │ N:1 (origin/dest)       └──────────────┘
       ├────────────────────────>┌──────────────┐
       │                         │  locations   │
       ▼                         └──────────────┘
┌──────────────┐
│  audit_logs  │
└──────────────┘
```

### Table Dictionary
1. **`users`**: User identities, hashed credentials, contact details, and RBAC roles (`admin`, `operator`, `customer`).
2. **`carriers`**: Shipping lines and air freight operators with UN/LOCODE carrier codes, contact data, transport mode (`SEA`, `AIR`, `ROAD`, `RAIL`, `MULTIMODAL`), and active toggles.
3. **`locations`**: Marine terminals, airports, and inland depots with UN/LOCODE identifiers, coordinates (latitude, longitude), and types (`PORT`, `AIRPORT`, `WAREHOUSE`, `INLAND_HUB`).
4. **`shipments`**: Primary consignment ledger containing unique tracking numbers (`TRC-YYYY-XXXXXX`), consignee relations, route IDs, cargo classification, metrics (weight kg, volume cbm), lifecycle state, schedules (`expected_departure`, `expected_arrival`, `estimated_arrival`, `actual_departure`, `actual_arrival`), and computed delay metrics.
5. **`shipment_items`**: Line-item manifest items specifying descriptions, HS tariff codes, quantities, container packaging, and hazardous cargo flags.
6. **`tracking_events`**: Immutable append-only chain of custody. Records milestone type, status, event timestamp, geocoordinates, notes, and the creating operator ID.
7. **`shipment_exceptions`**: Incident tracking disruptions (`CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE`) with status (`OPEN`, `INVESTIGATING`, `RESOLVED`), delay impact hours, and resolution notes.
8. **`notifications`**: User alert inbox for status changes, delay notifications, and incident dispatches.
9. **`audit_logs`**: Immutable security ledger recording actor ID, action type, entity type, entity ID, narrative description, IP address, user-agent, and JSON before/after snapshots.
10. **`system_settings`**: Key-value matrix controlling configurable delay severity thresholds and academic group scenario presets (`A`, `B`, `C`).

---

## 4. User Roles & Server-Side Permissions (Slide 05)

Every route, web controller action, and REST API endpoint validates authorization server-side:

| Capability | Administrator | Logistics Operator | Customer |
|---|:---:|:---:|:---:|
| **Dashboard Metrics** | Full Global | Full Operational | Scoped to Own Consignments |
| **Shipments Directory** | All Records | All Records | Own Consignments Only |
| **Create Shipment** | Yes | Yes | No (403 Forbidden) |
| **Edit Shipment** | Yes | Yes | No (403 Forbidden) |
| **Transition Status** | Yes | Yes | No (403 Forbidden) |
| **Append Tracking Event** | Yes | Yes | No (403 Forbidden) |
| **Log / Resolve Exceptions**| Yes | Yes | No (403 Forbidden) |
| **Delete Shipment** | Yes (Soft Delete) | No (403 Forbidden) | No (403 Forbidden) |
| **Manage Carriers** | Full CRUD | View Only | View Only |
| **Manage Locations** | Full CRUD + Geocoding | View Only | View Only |
| **Manage Users & Roles** | Full Access | No (403 Forbidden) | No (403 Forbidden) |
| **System Rules & Config** | Full Access | No (403 Forbidden) | No (403 Forbidden) |
| **Security Audit Logs** | Full Access | No (403 Forbidden) | No (403 Forbidden) |

---

## 5. Academic UAS Group Scenarios (Slide 20 & 23)

CargoTrace features built-in preset configurations corresponding to Slide 23 scenarios:

* **Group A Scenario (Activated by Default):**
  * **Route:** Southeast Asia (`Port of Tanjung Priok`, IDJKT) &rarr; Europe (`Port of Rotterdam`, NLRTM).
  * **Configured Delay Threshold:** 12.0 Hours.
  * **Primary Incident:** `CUSTOMS_HOLD` (Radiologic & document inspection hold in the Red Sea transit hub).
  * **Tracked KPI:** On-Time Delivery Rate & Delay Impact.
* **Group B Scenario:**
  * **Route:** Asia (`Port of Shanghai`, CNSHA) &rarr; North America (`Port of Los Angeles`, USLAX).
  * **Configured Delay Threshold:** 18.0 Hours.
  * **Primary Incident:** `WEATHER_DELAY` (Pacific tropical storm forcing speed reduction and 150 NM course detour).
  * **Tracked KPI:** Average Delay Duration.
* **Group C Scenario:**
  * **Route:** Southeast Asia (`Port of Singapore`, SGSIN) &rarr; Australia (`Port of Melbourne`, AUMEL).
  * **Configured Delay Threshold:** 24.0 Hours.
  * **Primary Incident:** `DOCUMENTATION_ISSUE` (Consular bill of lading discrepancy).
  * **Tracked KPI:** Exception Resolution Turnaround Time.

Administrators can switch scenario presets with one click under the **Rules & Config** web portal.

---

## 6. Seeded Demo Accounts (One-Click Testing)

Run `php artisan db:seed` to populate the database with realistic demo accounts:

| Role | Email | Password | Scope & Description |
|---|---|---|---|
| **Administrator** | `admin@cargotrace.test` | `password` | Complete administrative authority, user management, audit trail, scenario configuration |
| **Logistics Operator** | `operator@cargotrace.test` | `password` | Operations hub dispatcher; manages shipments, adds tracking waypoints, logs exceptions |
| **Customer (Primary)** | `customer@cargotrace.test` | `password` | PT Nusantara Global Trade; consignee for Southeast Asia to Europe route |
| **Customer (Secondary)**| `customer2@cargotrace.test` | `password` | Pacific Rim Imports Ltd; consignee for Transpacific and Australian routes |

---

## 7. REST API Documentation (Slide 12 & 13)

CargoTrace provides a complete RESTful API secured by Laravel Sanctum Bearer tokens. All endpoints enforce strict JSON request/response formats and standard HTTP status codes (`200 OK`, `201 Created`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `422 Unprocessable Entity`).

### Postman Collection
The collection is included in the project root:
* **File:** [`cargotrace_postman_collection.json`](file:///C:/Users/nurul/.gemini/antigravity/scratch/cargotrace/cargotrace_postman_collection.json)
* Import this file into Postman or Insomnia. The collection automatically persists the `auth_token` variable upon executing the `POST /api/login` request.

### Core Endpoints

#### Authentication
* `POST /api/register` – Register customer account (Returns 201 + token).
* `POST /api/login` – Authenticate with email/password (Returns 200 + token).
* `POST /api/logout` – Revoke active Sanctum token (Returns 200).
* `GET /api/user` – Get current authenticated profile (Returns 200).

#### Shipments
* `GET /api/shipments` – List shipments with search, filtering, and pagination. Scoped to customer.
* `POST /api/shipments` – Create new shipment with items manifest (Admin/Operator only; Returns 201).
* `GET /api/shipments/{id}` – Retrieve shipment details, route, items, and exceptions.
* `PUT /api/shipments/{id}` – Update shipment details (Admin/Operator only; Returns 200).
* `DELETE /api/shipments/{id}` – Soft-delete shipment (Admin only; Returns 200).
* `GET /api/shipments/{id}/tracking` – Retrieve chronological tracking timeline.
* `POST /api/shipments/{id}/tracking` – Append new tracking event and validate chronology (Returns 201).

#### Related Resources
* `GET /api/carriers` – List active logistics carriers.
* `POST /api/carriers` – Create logistics carrier (Admin only; Returns 201).
* `GET /api/locations` – List ports and geographic nodes.
* `POST /api/locations` – Create location with latitude/longitude (Admin only; Returns 201).
* `GET /api/shipments/{id}/exceptions` – List incidents and exceptions for shipment.
* `POST /api/shipments/{id}/exceptions` – Record incident, post tracking event, and recalculate ETA (Returns 201).
* `PATCH /api/exceptions/{id}` – Update exception status (`INVESTIGATING` / `RESOLVED`) with resolution notes (Returns 200).

#### External Integrations
* `GET /api/external/weather?latitude={lat}&longitude={lon}` – Real-time weather at coordinates via Open-Meteo API.
* `GET /api/external/geocode?q={query}` – Forward geocoding search via OpenStreetMap Nominatim API.

---

## 8. Installation & Setup Guide

### System Requirements
* PHP 8.2 or 8.3+ with `pdo`, `pdo_mysql` (or `pdo_sqlite`), `mbstring`, `curl`, and `openssl` extensions enabled.
* Composer 2.x
* MySQL 8.x (or SQLite 3 for zero-configuration local evaluation)

### Step-by-Step Local Setup

1. **Clone or Navigate to the Repository:**
   ```bash
   cd cargotrace
   ```

2. **Install PHP Dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment Variables:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration:**
   * **Option A: SQLite (Immediate zero-configuration setup):**
     Ensure `.env` contains:
     ```env
     DB_CONNECTION=sqlite
     ```
     Ensure the SQLite file exists:
     ```bash
     touch database/database.sqlite
     ```
   * **Option B: MySQL 8+ (Slide 14):**
     Create a database in MySQL (`cargotrace_db`) and update `.env`:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=cargotrace_db
     DB_USERNAME=root
     DB_PASSWORD=your_password
     ```

5. **Execute Database Migrations and Seed Demo Scenarios:**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Start the Local Development Server:**
   ```bash
   php artisan serve
   ```
   Access the web application at `http://localhost:8000`.

---

## 9. Running Automated Test Suite (Slide 16)

The project includes 35 comprehensive automated feature tests covering authentication, lifecycle state machine constraints, deterministic delay calculations, RBAC authorization, and REST API contracts:

```bash
php vendor/phpunit/phpunit/phpunit
```
*or via Artisan:*
```bash
php artisan test
```

### Test Suite Summary:
* `AuthenticationTest` (8 tests): Web login, invalid credentials rejection, guest protection, Sanctum API token generation and revocation.
* `ShipmentLifecycleTest` (4 tests): Sequential status progression, illegal transition prevention, terminal state immutability, chronological append-only tracking enforcement.
* `EtaAndDelayTest` (5 tests): On-time zero-variance evaluation, Low/Medium/High delay severity calculations, configurable threshold adjustments.
* `ShipmentAuthorizationTest` (5 tests): Consignee data isolation, forbidden cross-customer viewing (403), deletion restriction.
* `ApiShipmentEndpointTest` (10 tests): Full REST endpoint contract validation (200, 201, 401, 403, 404, 422), customer query scoping, and exception resolution.
* `ExampleTest` (1 test): Application dashboard response validation.

**Result:** `35 passed (125 assertions)` with 100% pass rate.

---

## 10. Code Ownership & Defense Interview Guide (Slide 19)

During the UAS presentation and code ownership interview, be prepared to explain:

1. **Why is the relationship between `Shipment` and `TrackingEvent` a `hasMany` relationship?**  
   *Answer:* A single shipment travels through multiple checkpoints and milestones over time. Each tracking event is an immutable historical record. We never overwrite the previous tracking record when adding a new one (Slide 07 append-only rule), which allows reconstructing the exact chronological audit trail of custody.

2. **How does the system prevent invalid status transitions?**  
   *Answer:* Inside `app/Services/ShipmentService.php`, the `$allowedTransitions` map defines the state machine rules:
   * `CREATED` &rarr; `BOOKED`, `CANCELLED`
   * `BOOKED` &rarr; `PICKED_UP`, `CANCELLED`
   * `PICKED_UP` &rarr; `IN_TRANSIT`, `CANCELLED`
   * `IN_TRANSIT` &rarr; `DELIVERED`
   * `DELIVERED` &rarr; Terminal (no forward or backward transitions allowed).  
   If an operator attempts an illegal transition (e.g. `CREATED` &rarr; `DELIVERED` or reverting `DELIVERED` back to `IN_TRANSIT`), `validateTransition()` throws a `DomainException`, which the controllers catch and return as a clean `422 Unprocessable Entity` or session error banner without exposing stack traces.

3. **How does the deterministic ETA / delay engine work?**  
   *Answer:* In `app/Services/EtaService.php`, `evaluateDelay()` compares `estimated_arrival` with `expected_arrival`. If `estimated_arrival > expected_arrival`, it calculates `delayHours = round(diffInMinutes / 60, 2)`. It then categorizes severity based on configurable thresholds retrieved dynamically from `system_settings`:
   * 0 to 6h: `LOW`
   * >6 to 24h: `MEDIUM`
   * >24h: `HIGH`
   When an exception (e.g. Customs Hold with 14 hours impact) is logged in `ExceptionService`, `impact_hours` is added to the shipment's ETA, automatically triggering delay recalculation and firing an in-app alert notification.

4. **How is customer data privacy strictly protected?**  
   *Answer:* Both web queries and API queries inspect `$user->role`. If the authenticated user is a `customer`, queries automatically append `->where('customer_id', $user->id)`. Furthermore, `ShipmentPolicy::view` checks if `$shipment->customer_id === $user->id`, aborting with HTTP 403 Forbidden if a customer attempts to query another client's shipment ID.

---

## 11. External Attributions & Licensing

* **Leaflet.js:** Open-source JavaScript library for interactive maps (BSD 2-Clause License).
* **OpenStreetMap:** Map data &copy; [OpenStreetMap contributors](https://www.openstreetmap.org/copyright) (ODbL License).
* **Open-Meteo API:** Open-source weather API provided under Attribution 4.0 International (CC BY 4.0).
* **Tailwind CSS & Chart.js:** Open-source UI styling and chart rendering libraries (MIT License).
* **Laravel Framework:** Open-source PHP web framework created by Taylor Otwell (MIT License).
