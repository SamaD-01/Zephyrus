import paho.mqtt.client as mqtt
import json
import time
import random
from datetime import datetime

MQTT_BROKER = "localhost"
MQTT_PORT = 1883

DEVICE_ID = "sensor-001"
DEVICE_NAME = "Living Room Sensor"

MQTT_TOPIC = f"zephyrus/sensors/{DEVICE_ID}"

BASE_TEMP = 22.0
BASE_HUMIDITY = 45.0
BASE_CO2 = 800
BASE_NOISE = 40.0

def generate_sensor_data():

    temp_variation = random.uniform(-2, 2)
    humidity_variation = random.uniform(-5, 5)
    co2_variation = random.randint(-100, 100)
    noise_variation = random.uniform(-5, 5)
    
    data = {
        "deviceId": DEVICE_ID,
        "temperature": round(BASE_TEMP + temp_variation, 2),
        "humidity": round(BASE_HUMIDITY + humidity_variation, 2),
        "co2": BASE_CO2 + co2_variation,
        "noise": round(BASE_NOISE + noise_variation, 2),
        "timestamp": datetime.utcnow().strftime("%Y-%m-%dT%H:%M:%S+00:00")
    }
    
    return data

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print("Connected to MQTT Broker!")
        print(f"Device: {DEVICE_NAME} ({DEVICE_ID})")
        client.publish("zephyrus/status", "online")
    else:
        print(f"Failed to connect, return code {rc}")

def main():
    print("Zephyrus Sensor Simulator Starting...")
    print(f"Connecting to MQTT broker at {MQTT_BROKER}:{MQTT_PORT}")
    print(f"Make sure device '{DEVICE_ID}' is registered in Zephyrus!")
    print()
    
    client = mqtt.Client(client_id=DEVICE_ID)
    client.on_connect = on_connect
    
    try:
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        client.loop_start()
        
        print(f"Publishing sensor data to topic: {MQTT_TOPIC}")
        print("Press Ctrl+C to stop\n")
        
        while True:
            data = generate_sensor_data()
            payload = json.dumps(data)
            
            result = client.publish(MQTT_TOPIC, payload)
            
            if result.rc == 0:
                print(f"Published: Temp={data['temperature']}°C, "
                      f"Humidity={data['humidity']}%, "
                      f"CO2={data['co2']}ppm, "
                      f"Noise={data['noise']}dB")
            else:
                print(f"Failed to publish: {result.rc}")
            
            time.sleep(5)  
            
    except KeyboardInterrupt:
        print("\nStopping simulator...")
        client.publish("zephyrus/status", "offline")
        client.loop_stop()
        client.disconnect()
        print("Goodbye!")
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()