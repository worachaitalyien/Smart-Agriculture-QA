# Smart-Agriculture-QA — Lab 10

**6840202946 วรชัย ตาลเยี่ยน**

This repository includes hardware/, ai_engine/, backend/, mobile_app/ and docs/ as specified in the manual.

Repository: https://github.com/worachaitalyien/Smart-Agriculture-QA (public; no login needed to read). Real teammate review is still pending.

## What is real and what is simulated
- PHP REST API, MySQL persistence, Flutter dashboard and MQTT transport run as real software.
- In the accompanying local demo, temperature/humidity and fruit counts are synthetic inputs explicitly labelled SIMULATION.
- No physical board measurement, camera-based fruit evaluation, trained best.pt, teammate review or hosted repository URL is fabricated.

## Backend
Import backend/smart_farm_db.sql with MySQL/MariaDB. Copy backend/config.example.php to backend/config.php and set your connection. Start `php -S 127.0.0.1:8078 -t backend`.
POST /api/store_telemetry.php takes {"temperature":28.5,"humidity":65}; POST /api/store_inventory.php takes {"apple":2,"mango":1,"orange":3}. Both JSON and form fields are accepted, because the manual code uses JSON while its result table lists form protocols. GET /api/get_latest.php returns nullable telemetry/inventory until a first insert.

## Hardware
Wire DHT data to GPIO 4, VCC to 3.3 V, GND to GND, with a 10 kΩ pull-up between DATA and VCC. Copy hardware/smart_farm/config.example.h to config.h and set WiFi/API URL. SENSOR_TYPE is DHT11 by default; change it to DHT22 for that sensor. Install ESP32 core, ArduinoJson, DHT sensor library and Adafruit Unified Sensor, then compile/upload. Capture real serial evidence only after testing a board.

## Flutter
The project already includes Android and web scaffolding. Run `flutter pub get` inside mobile_app, then `flutter run -d chrome --dart-define=API_URL=http://127.0.0.1:8078/api/get_latest.php`. On Android use a LAN-accessible API URL (emulator host commonly 10.0.2.2). INTERNET permission and debug-only cleartext access are included.

## AI
The runnable simulation uses no heavy packages: `python ai_engine/detector.py --simulate --once`. For actual YOLOv8 install ai_engine/requirements.txt, provide a labelled 3-class dataset and train using train.py. Supply resulting weights to `detector.py --weights path/to/best.pt --source 0`. Generic COCO weights do not have a mango class, so they are not accepted as a replacement.

## Git workflow
Create branches such as feature/hardware, feature/php-api, feature/flutter-ui. Push to your group's actual GitHub repository, open PRs, request at least one real teammate's review and merge only after that review. The included PR template supports this workflow. A local Git commit is not a published GitHub submission.

See docs/answers.md and docs/experiment_results.csv. Record real measurements separately from simulation observations.

## Submission evidence

[Thai A4 lab report and manual result tables](https://github.com/worachaitalyien/Smart-Agriculture-QA/blob/feature/integration-validation/docs/submission.pdf) are on the validation branch. [PR #1](https://github.com/worachaitalyien/Smart-Agriculture-QA/pull/1) contains measured software results and is awaiting a real teammate review. Physical hardware, camera inference and trained fruit weights remain untested/unavailable as stated in the report.
