# Smart Agriculture Flutter Dashboard

6840202946 วรชัย ตาลเยี่ยน

Reads telemetry and fruit inventory from PHP/MySQL every three seconds. Handles empty data, failed requests and widget disposal without overlapping polls. The companion software demo uses explicitly synthetic producers.

Tested with Flutter 3.47.7 / Dart 3.13.5. Start the backend using the repository README, then run here:

```text
flutter pub get
flutter analyze
flutter run -d chrome --dart-define=API_URL=http://127.0.0.1:8078/api/get_latest.php
```

Android and web project files are included. On a physical Android device use a reachable LAN API URL. The combined CPE.zip includes a compiled web build and local launcher, so Flutter is only needed to rebuild or develop the app.
