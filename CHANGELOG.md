# Changelog

All notable changes to the **CargoTrace** Global Shipment Visibility Platform are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-11-18
### Added
- Complete UAS Final Project submission package conforming to Web Programming 2 requirements.
- Full-stack Laravel 12 application with MySQL 8+ / SQLite dual compatibility.
- 10 Relational tables with strict foreign keys, cascade rules, and soft deletes.
- Strict lifecycle state machine (`CREATED` -> `BOOKED` -> `PICKED_UP` -> `IN_TRANSIT` -> `DELIVERED`).
- Append-only chronological tracking timeline with geographic coordinate mapping.
- Deterministic ETA and schedule delay categorization matrix (Low, Medium, High).
- Incident and exception management workflow across 6 maritime disruption types.
- Interactive Leaflet.js cartography with OpenStreetMap tiles and polyline routing.
- Real-time environmental weather integration via Open-Meteo REST API.
- Nominatim forward geocoding integration with auto-fill coordinate search.
- 21 RESTful API endpoints secured by Laravel Sanctum Bearer tokens.
- Complete Postman collection with automated test scripts and environment variables.
- Comprehensive automated test suite comprising 37 feature and unit tests with 100% pass rate.
- Multi-stage production Dockerfile and Docker Compose orchestration.
- GitHub Actions CI/CD workflows for automated testing and code quality linting.
- Comprehensive English documentation including ERD, Architecture, REST API, and Defense Guide.

## [0.9.0] - 2026-11-10
### Added
- System audit trail recording actor identity, action type, IP address, and JSON state changes.
- Student group scenario selector (Presets A, B, and C) for customizable evaluation parameters.
- In-app notification bell with real-time incident and status change badges.

## [0.5.0] - 2026-10-20
### Added
- Core Service Layer architecture (`ShipmentService`, `TrackingService`, `EtaService`, `ExceptionService`).
- Multi-role RBAC authorization policy for Administrator, Logistics Operator, and Customer.
- Live dashboard displaying real database KPI metrics and Chart.js graphs.

## [0.1.0] - 2026-10-02
### Added
- Project initialization with Laravel 12 scaffold and database migrations.
