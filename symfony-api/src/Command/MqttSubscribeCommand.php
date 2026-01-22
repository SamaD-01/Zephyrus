<?php

namespace App\Command;

use App\Entity\SensorReading;
use Doctrine\ORM\EntityManagerInterface;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mqtt:subscribe',
    description: 'Subscribe to MQTT broker and save sensor readings',
)]
class MqttSubscribeCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
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

                    $reading = new SensorReading();
                    $reading->setDeviceId($data['deviceId'] ?? 'unknown');
                    $reading->setTemperature($data['temperature'] ?? 0.0);
                    $reading->setHumidity($data['humidity'] ?? 0.0);
                    $reading->setCo2($data['co2'] ?? 0);
                    $reading->setNoise($data['noise'] ?? 0.0);
                    $reading->setTimestamp(new \DateTimeImmutable($data['timestamp'] ?? 'now'));

                    $this->entityManager->persist($reading);
                    $this->entityManager->flush();

                    $io->success("Saved reading from {$reading->getDeviceId()}");
                    
                    if ($reading->getCo2() > 1000) {
                        $io->warning("HIGH CO2 LEVEL: {$reading->getCo2()} ppm");
                    }
                    if ($reading->getTemperature() > 30) {
                        $io->warning("HIGH TEMPERATURE: {$reading->getTemperature()}°C");
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