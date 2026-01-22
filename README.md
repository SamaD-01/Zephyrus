# Zephyrus - Smart Air Quality & Safety Monitoring System

[![Symfony](https://img.shields.io/badge/Symfony-8-000000?style=flat&logo=symfony)](https://symfony.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-336791?style=flat&logo=postgresql)](https://www.postgresql.org/)
[![MQTT](https://img.shields.io/badge/MQTT-Eclipse_Mosquitto-3C5280?style=flat&logo=eclipsemosquitto)](https://mosquitto.org/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> An intelligent IoT system that monitors air quality and environmental conditions in real-time, featuring live dashboards, threshold alerts, and comprehensive data analytics.

![Zephyrus Dashboard](docs/screenshots/dashboard.png)

## 🚀 Features

- **Real-time Monitoring**: Track temperature, humidity, CO2 levels, and noise in real-time
- **IoT Integration**: MQTT-based sensor communication for scalable device management
- **Live Dashboard**: Beautiful, responsive dashboard with auto-refreshing charts
- **Smart Alerts**: Color-coded status indicators for quick health assessment
- **Data Persistence**: PostgreSQL database for historical data analysis
- **Flexible Architecture**: Easy to add new sensors and monitoring parameters

## 🛠️ Tech Stack

### Backend
- **Symfony 8** - Modern PHP framework
- **PostgreSQL 15** - Robust relational database
- **Doctrine ORM** - Database abstraction layer
- **PHP 8.2+** - Latest PHP features

### IoT Layer
- **Eclipse Mosquitto** - MQTT message broker
- **php-mqtt/client** - MQTT client library
- **Python 3.x** - Sensor simulation

### Frontend
- **Chart.js** - Interactive data visualization
- **Vanilla JavaScript** - No framework overhead
- **Responsive CSS** - Mobile-friendly design

### DevOps
- **Docker** - Containerized MQTT broker
- **Git** - Version control

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- PostgreSQL 15+
- Docker
- Python 3.x (for sensor simulator)
- Git

## 🔧 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/SamaD-01/zephyrus.git
cd zephyrus
```

### 2. Install Dependencies

```bash
cd symfony-api
composer install
```

### 3. Configure Environment

Copy the example environment file and configure your database:

```bash
cp .env.example .env
```

Edit `.env`:
```env
DATABASE_URL="postgresql://YOUR_USER:YOUR_PASSWORD@127.0.0.1:5432/zephyrus?serverVersion=15&charset=utf8"
```

### 4. Set Up Database

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Start MQTT Broker

```bash
docker-compose up -d
```

### 6. Install Python Dependencies

```bash
cd iot-simulation
pip install paho-mqtt
```

## 🚀 Running the Application

You need **3 terminals** running simultaneously:

### Terminal 1: Symfony Web Server
```bash
cd symfony-api
symfony server:start
```
Access dashboard at: `http://127.0.0.1:8000`

### Terminal 2: MQTT Subscriber
```bash
cd symfony-api
php bin/console mqtt:subscribe
```

### Terminal 3: Sensor Simulator
```bash
cd iot-simulation
python sensor_simulator.py
```

## Project Structure

```
zephyrus/
├── symfony-api/              # Symfony backend
│   ├── src/
│   │   ├── Command/         # MQTT subscriber command
│   │   ├── Controller/      # API & Dashboard controllers
│   │   ├── Entity/          # Database entities
│   │   └── Repository/      # Data repositories
│   ├── templates/           # Twig templates
│   ├── docker-compose.yml   # MQTT broker configuration
│   └── mosquitto.conf       # Mosquitto settings
├── iot-simulation/          # IoT sensor simulator
│   └── sensor_simulator.py  # Python MQTT publisher
└── docs/
```

## 🔌 API Endpoints

### Get Latest Readings
```http
GET /api/readings/latest
```

**Response:**
```json
[
  {
    "id": 1,
    "deviceId": "simulator-sensor-001",
    "temperature": 22.5,
    "humidity": 45.0,
    "co2": 800,
    "noise": 40.2,
    "timestamp": "2026-01-16 12:30:45"
  }
]
```

### Get Chart Data
```http
GET /api/readings/chart
```

**Response:**
```json
{
  "labels": ["12:30:45", "12:30:50", ...],
  "datasets": [...]
}
```

## 🎨 Dashboard Features

- **Real-time Cards**: Live sensor values with color-coded status
  - 🟢 Green: Normal
  - 🟡 Yellow: Warning
  - 🔴 Red: Critical

- **Charts**: Multi-line chart showing all metrics over time
- **Auto-refresh**: Updates every 5 seconds
- **Responsive Design**: Works on desktop, tablet, and mobile

## 🧪 Testing

### Test MQTT Connection
```bash
# Subscribe to test topic
docker exec -it zephyrus_mqtt mosquitto_sub -h localhost -t "test/topic"

# Publish test message (in another terminal)
docker exec -it zephyrus_mqtt mosquitto_pub -h localhost -t "test/topic" -m "Hello!"
```

### Test Database Connection
```bash
php bin/console dbal:run-sql "SELECT COUNT(*) FROM sensor_reading"
```

## 🔐 Security Considerations

**For Production:**
- Change default MQTT credentials
- Enable MQTT authentication
- Use HTTPS/WSS for web traffic
- Implement user authentication
- Add rate limiting
- Enable CORS properly
- Use environment variables for secrets

## 🐛 Troubleshooting

### MQTT Connection Failed
```bash
# Check if container is running
docker ps

# Restart MQTT broker
docker-compose restart mqtt

# Check logs
docker logs zephyrus_mqtt
```

### Database Connection Error
```bash
# Verify PostgreSQL is running
# Check DATABASE_URL in .env
php bin/console doctrine:database:create
```

### Charts Not Showing Data
- Check browser console (F12) for errors
- Verify API endpoints return data: `/api/readings/chart`
- Ensure sensor simulator is running

## 🚀 Future Enhancements

- [ ] Discord/Email alert system
- [ ] Multi-device management panel
- [ ] AI anomaly detection
- [ ] Historical data export (CSV/Excel)
- [ ] Multi-room/location support
- [ ] Mobile app
- [ ] Cloud deployment (AWS/Heroku)
- [ ] Real ESP32 hardware integration

## 📖 Documentation

- [Architecture Overview](docs/ARCHITECTURE.md)
- [API Documentation](docs/API.md)
- [Setup Guide](docs/SETUP.md)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Abdessamad**
- GitHub: [@SamaD-01](https://github.com/SamaD-01)
- LinkedIn: [Abdessamad AJJA](https://linkedin.com/in/abdessamad-ajja)
- Portfolio: [ajja.surge.sh](https://ajja.surge.sh)

## 🙏 Acknowledgments

- Symfony Framework
- Eclipse Mosquitto
- Chart.js
- PostgreSQL Community

---

⭐ **Star this repository if you find it helpful!**