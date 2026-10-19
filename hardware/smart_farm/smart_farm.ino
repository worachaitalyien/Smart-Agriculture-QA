// 6840202946 วรชัย ตาลเยี่ยน — Lab 10 (real ESP32 firmware)
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#if __has_include("config.h")
#include "config.h"
#else
#include "config.example.h"
#endif
DHT dht(DHT_PIN,SENSOR_TYPE);
unsigned long lastSend=0;
void setup(){
 Serial.begin(115200);dht.begin();WiFi.mode(WIFI_STA);WiFi.begin(WIFI_SSID,WIFI_PASSWORD);
 Serial.println("Lab10 6840202946: connecting WiFi");
}
void loop(){
 if(WiFi.status()!=WL_CONNECTED){delay(500);return;}
 if(millis()-lastSend<10000)return;lastSend=millis();
 float t=dht.readTemperature(),h=dht.readHumidity();
 if(isnan(t)||isnan(h)){Serial.println("DHT read failed; no data sent");return;}
 JsonDocument doc;doc["temperature"]=t;doc["humidity"]=h;String payload;serializeJson(doc,payload);
 HTTPClient http;http.setConnectTimeout(5000);http.setTimeout(5000);http.begin(TELEMETRY_URL);http.addHeader("Content-Type","application/json");
 int code=http.POST(payload);Serial.printf("POST %s -> HTTP %d\n",payload.c_str(),code);
 if(code>0)Serial.println(http.getString());http.end();
}
