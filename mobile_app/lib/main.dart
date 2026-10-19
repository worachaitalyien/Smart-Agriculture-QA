import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

const api = String.fromEnvironment('API_URL',
    defaultValue: 'http://127.0.0.1:8078/api/get_latest.php');
void main() => runApp(const FarmApp());

class FarmApp extends StatelessWidget {
  const FarmApp({super.key});
  @override
  Widget build(BuildContext context) => MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Smart Agriculture QA',
      theme: ThemeData(colorSchemeSeed: Colors.green, useMaterial3: true),
      home: const Dashboard());
}

class Dashboard extends StatefulWidget {
  const Dashboard({super.key});
  @override
  State<Dashboard> createState() => _DashboardState();
}

class _DashboardState extends State<Dashboard> {
  Timer? timer;
  Map<String, dynamic>? telemetry, inventory;
  String? error;
  bool loading = true, inFlight = false;
  @override
  void initState() {
    super.initState();
    fetch();
    timer = Timer.periodic(const Duration(seconds: 3), (_) => fetch());
  }

  @override
  void dispose() {
    timer?.cancel();
    super.dispose();
  }

  Future<void> fetch() async {
    if (inFlight) return;
    inFlight = true;
    try {
      final r =
          await http.get(Uri.parse(api)).timeout(const Duration(seconds: 8));
      final v = jsonDecode(utf8.decode(r.bodyBytes)) as Map<String, dynamic>;
      if (r.statusCode != 200 || v['status'] != 'success')
        throw Exception(v['message'] ?? 'HTTP ${r.statusCode}');
      if (mounted)
        setState(() {
          telemetry = v['telemetry'] as Map<String, dynamic>?;
          inventory = v['inventory'] as Map<String, dynamic>?;
          loading = false;
          error = null;
        });
    } catch (e) {
      if (mounted)
        setState(() {
          error = '$e';
          loading = false;
        });
    } finally {
      inFlight = false;
    }
  }

  Widget tile(String label, Object? value, String unit, IconData icon,
          Color color) =>
      Card(
          child: ListTile(
              leading: Icon(icon, color: color, size: 34),
              title: Text(label),
              trailing: Text(value == null ? '—' : '$value $unit',
                  style: const TextStyle(
                      fontSize: 23, fontWeight: FontWeight.bold))));
  @override
  Widget build(BuildContext context) => Scaffold(
      appBar: AppBar(title: const Text('Smart Agriculture QA'), actions: [
        IconButton(
            onPressed: fetch,
            tooltip: 'Refresh',
            icon: const Icon(Icons.refresh))
      ]),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : Center(
              child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 850),
                  child: ListView(padding: const EdgeInsets.all(18), children: [
                    const Text('6840202946 วรชัย ตาลเยี่ยน'),
                    const SizedBox(height: 12),
                    if (error != null)
                      Card(
                          color: Colors.orange.shade50,
                          child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Text(
                                  'Connection error: $error\nRetrying every 3 seconds.'))),
                    if (telemetry == null && inventory == null && error == null)
                      const Padding(
                          padding: EdgeInsets.all(16),
                          child: Text(
                              'No readings yet. Waiting for the first API submission.')),
                    tile('Temperature', telemetry?['temperature'], '°C',
                        Icons.thermostat, Colors.red),
                    tile('Humidity', telemetry?['humidity'], '%',
                        Icons.water_drop, Colors.blue),
                    const Divider(height: 30),
                    tile('Apples', inventory?['apple_count'], 'pcs',
                        Icons.apple, Colors.red),
                    tile('Mangoes', inventory?['mango_count'], 'pcs', Icons.eco,
                        Colors.green),
                    tile('Oranges', inventory?['orange_count'], 'pcs',
                        Icons.circle, Colors.orange),
                    const SizedBox(height: 16),
                    Text(
                        'Telemetry: ${telemetry?["created_at"] ?? "not received"}'),
                    Text(
                        'Inventory: ${inventory?["created_at"] ?? "not received"}'),
                    const Text(
                        'Refresh every 3 seconds · values are read from the PHP/MySQL API')
                  ]))));
}
