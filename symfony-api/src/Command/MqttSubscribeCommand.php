<?php

namespace App\Command;

use App\Entity\SensorReading;
use App\Repository\UserRepository;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\AlertService;

#[AsCommand(
    name: 'mqtt:subscribe',
    description: 'Subscribe to MQTT broker and save sensor readings',
)]
class MqttSubscribeCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private DeviceRepository $deviceRepository,
        private AlertService $alertService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Zephyrus MQTT Subscriber');
        $io->info('Connecting to MQTT broker at localhost:1883...');

        $mqtt = new MqttClient('127.0.0.1', 1883, 'symfony-subscriber');

        $connectionSettings = (new ConnectionSettings())
            ->setKeepAliveInterval(60)
            ->setLastWillTopic('zephyrus/status')
            ->setLastWillMessage('offline')
            ->setLastWillQualityOfService(1);

        try {
            $mqtt->connect($connectionSettings, true);
            $io->success('Connected to MQTT broker!');
            $io->info('Listening for sensor data on topic: zephyrus/sensors/#');

            $mqtt->subscribe('zephyrus/sensors/#', function (string $topic, string $message) use ($io) {
                $io->writeln("Received from {$topic}: {$message}");
                
                try {
                    $data = json_decode($message, true);
                    
                    if (!$data) {
                        $io->warning('Invalid JSON received');
                        return;
                    }

                    $deviceId = $data['deviceId'] ?? 'unknown';

                    $device = $this->deviceRepository->findOneBy(['deviceId' => $deviceId]);

                    if (!$device) {
                        $io->warning("Device '{$deviceId}' not registered. Please register it first at /devices/new");
                        return;
                    }

                    if (!$device->isActive()) {
                        $io->warning("Device '{$deviceId}' is disabled. Skipping reading.");
                        return;
                    }

                    $reading = new SensorReading();
                    $reading->setDeviceId($deviceId);
                    $reading->setTemperature($data['temperature'] ?? 0.0);
                    $reading->setHumidity($data['humidity'] ?? 0.0);
                    $reading->setCo2($data['co2'] ?? 0);
                    $reading->setNoise($data['noise'] ?? 0.0);
                    $reading->setTimestamp(new \DateTimeImmutable($data['timestamp'] ?? 'now'));
                    $reading->setUser($device->getUser());
                    $reading->setDevice($device);

                    $this->entityManager->persist($reading);
                    $this->entityManager->flush();

                    $io->success("Saved reading from {$device->getName()} ({$deviceId})");

                    $alerts = $this->alertService->checkThresholds($reading, $device);

                    $io->writeln("Debug: Created " . count($alerts) . " alerts");

                    foreach ($alerts as $alert) {
                        $icon = $alert->getSeverityIcon();
                        $io->warning("{$icon} ALERT: {$alert->getMessage()}");
                        $io->writeln("Debug: Alert ID: " . ($alert->getId() ?? 'NOT PERSISTED'));
                    }
                } catch (\Exception $e) {
                    $io->error('Error saving reading: ' . $e->getMessage());
                }
            }, 0);

            $mqtt->loop(true);
            $mqtt->disconnect();

        } catch (\Exception $e) {
            $io->error('MQTT Error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
