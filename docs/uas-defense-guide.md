# UAS Defense & Code Ownership Interview Guide (Slide 19)

This guide provides direct, evidence-backed answers to anticipated examiner questions during the final UAS defense interview.

### Question 1: "Why is the relationship between Shipment and TrackingEvent hasMany instead of hasOne?"
*Answer:* A shipment traverses multiple sequential checkpoints (Port departure, canal passage, mid-voyage checkpoints, customs gate-in, and final release). To preserve the legal chain of custody and satisfy Slide 07 requirements, tracking events are **strictly append-only**. Event history must never be overwritten, allowing full chronological replay.

### Question 2: "Where and how is the status transition validated?"
*Answer:* In `app/Services/ShipmentService.php` within `validateTransition()`. A lookup array `$allowedTransitions` enforces permissible next states:
* `CREATED` -> `BOOKED`, `CANCELLED`
* `BOOKED` -> `PICKED_UP`, `CANCELLED`
* `PICKED_UP` -> `IN_TRANSIT`, `CANCELLED`
* `IN_TRANSIT` -> `DELIVERED`
* `DELIVERED` -> Terminal state.
Attempts to breach this flow throw a `DomainException`, which the controllers catch and render as clean HTTP 422 JSON errors or flash banners without leaking stack traces.

### Question 3: "Explain how the deterministic delay calculation works."
*Answer:* In `app/Services/EtaService.php`, `evaluateDelay()` compares `estimated_arrival` with `expected_arrival`. When estimated arrival exceeds expected arrival:
1. `delay_hours = round(diffInMinutes / 60, 2)`
2. Severity is classified against configurable thresholds stored in `system_settings`:
   * `LOW`: <= 6.0 hours
   * `MEDIUM`: 6.0h - 24.0 hours
   * `HIGH`: > 24.0 hours
3. Logging an exception in `ExceptionService` with `impact_hours` dynamically extends the shipment's ETA and triggers automatic customer notifications.
