# UAS Student Group Scenarios Matrix (Slide 23)

CargoTrace accommodates group-specific grading requirements through runtime configurable business rules:

| Parameter | Group A Scenario (Default) | Group B Scenario | Group C Scenario |
|---|---|---|---|
| **Trade Lane** | Southeast Asia &rarr; Europe | Asia &rarr; North America | Southeast Asia &rarr; Australia |
| **Origin Hub** | Tanjung Priok (IDJKT) | Shanghai (CNSHA) | Singapore (SGSIN) |
| **Destination**| Rotterdam (NLRTM) | Los Angeles (USLAX) | Melbourne (AUMEL) |
| **Delay Threshold**| 12.0 Hours | 18.0 Hours | 24.0 Hours |
| **Primary Exception**| `CUSTOMS_HOLD` | `WEATHER_DELAY` | `DOCUMENTATION_ISSUE` |
| **Focus Metric KPI**| On-time Delivery Rate | Average Delay Duration | Incident Resolution Turnaround |

Admins can activate any group scenario preset dynamically via the Web UI (`/settings`) or through API settings.
