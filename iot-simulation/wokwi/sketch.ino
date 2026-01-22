#include <WiFi.h>
#include <PubSubClient.h>
#include <DHT.h>
#include <ArduinoJson.h>

const char* ssid = "Wokwi-GUEST";
const char* password = "";

const char* mqtt_server = "IP_HERE"; 
const int mqtt_port = 1883;
const char* mqtt_topic = "zephyrus/sensors/esp32-001";

#define DHTPIN 15
#define DHTTYPE DHT22
#define MQ135_PIN 34
#define SOUND_PIN 35

DHT dht(DHTPIN, DHTTYPE);
WiFiClient espClient;
PubSubClient client(espClient);

String deviceId = "esp32-sensor-001";
unsigned long lastMsg = 0;

void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    if (client.connect(deviceId.c_str())) {
      Serial.println("connected");
      client.publish("zephyrus/status", "online");
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  
  pinMode(MQ135_PIN, INPUT);
  pinMode(SOUND_PIN, INPUT);
  
  dht.begin();
  setup_wifi();
  
  client.setServer(mqtt_server, mqtt_port);
  
  Serial.println("Zephyrus Sensor Starting...");
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  unsigned long now = millis();
  if (now - lastMsg > 5000) {  
    lastMsg = now;
    
    float temperature = dht.readTemperature();
    float humidity = dht.readHumidity();
    
    int rawCO2 = analogRead(MQ135_PIN);
    int co2 = map(rawCO2, 0, 4095, 400, 2000);
    
    int rawNoise = analogRead(SOUND_PIN);
    float noise = map(rawNoise, 0, 4095, 30, 90);
    
    StaticJsonDocument<256> doc;
    doc["deviceId"] = deviceId;
    doc["temperature"] = temperature;
    doc["humidity"] = humidity;
    doc["co2"] = co2;
    doc["noise"] = noise;
    doc["timestamp"] = "2026-01-16T12:00:00+00:00";  
    
    char buffer[256];
    serializeJson(doc, buffer);
    
    Serial.print("Publishing: ");
    Serial.println(buffer);
    client.publish(mqtt_topic, buffer);
    
    Serial.printf("Temperature: %.1f°C | Humidity: %.1f%% | CO2: %d ppm | Noise: %.1f dB\n",
                  temperature, humidity, co2, noise);
  }
}