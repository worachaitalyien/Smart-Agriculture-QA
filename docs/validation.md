# Software validation — 19 October 2026

Student: 6840202946 วรชัย ตาลเยี่ยน

| Check | Actual result |
|---|---|
| Flutter analyze | No issues found |
| Flutter web release build | Built successfully with locally bundled web resources |
| PHP syntax | All backend PHP files passed php -l |
| ESP32 smart_farm compile | Passed for esp32:esp32:esp32, core 3.3.7; flash 1,035,784 bytes; globals 48,376 bytes |
| JSON/form telemetry and inventory | Real PHP/MariaDB inserts and latest reads passed |
| Invalid input | Fractional/negative fruit counts, humidity >100, NaN, missing humidity rejected |
| Python detector --simulate --once | Sent synthetic counts apple=2, mango=1, orange=3 successfully |
| Flutter dashboard | Read saved 28.5 C, 65% and 2/1/3 counts through the actual API |

The database was MariaDB 10.4.32, PHP 8.2.12; Flutter 3.47.7 / Dart 3.13.5. Services bound to localhost. The companion CPE package contains the repeatable API test script, raw request timings and labelled screenshots.

## Limits that remain

No board was connected or flashed. No physical sensor, COM port, WiFi hotspot, camera inference, trained fruit weights or end-to-end hardware latency was measured. Do not interpret compilation or synthetic input as those measurements. No teammate review has happened; this validation branch is ready for a real peer review.
