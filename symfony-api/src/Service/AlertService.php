<?php

namespace App\Service;

use App\Entity\Alert;
use App\Entity\Device;
use App\Entity\SensorReading;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Service\NotificationService;

class AlertService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private NotificationService $notificationService
    ) {}

    public function checkThresholds(SensorReading $reading, Device $device): array
    {
        $alerts = [];

        $maxTemp = $device->getMaxTemperature() ?? 30.0;
        $minTemp = $device->getMinTemperature() ?? 10.0;
        $maxCo2 = $device->getMaxCo2() ?? 1000;
        $maxNoise = $device->getMaxNoise() ?? 70.0;

        if ($reading->getTemperature() > $maxTemp) {
            $severity = $reading->getTemperature() > ($maxTemp + 5) ? 'critical' : 'warning';
            $alerts[] = $this->createAlert(
                device: $device,
                reading: $reading,
                type: 'high_temperature',
                severity: $severity,
                message: sprintf(
                    'High temperature detected: %.1f°C (threshold: %.1f°C)',
                    $reading->getTemperature(),
                    $maxTemp
                ),
                value: $reading->getTemperature(),
                threshold: $maxTemp
            );
        }

        if ($reading->getTemperature() < $minTemp) {
            $severity = $reading->getTemperature() < ($minTemp - 5) ? 'critical' : 'warning';
            $alerts[] = $this->createAlert(
                device: $device,
                reading: $reading,
                type: 'low_temperature',
                severity: $severity,
                message: sprintf(
                    'Low temperature detected: %.1f°C (threshold: %.1f°C)',
                    $reading->getTemperature(),
                    $minTemp
                ),
                value: $reading->getTemperature(),
                threshold: $minTemp
            );
        }

        if ($reading->getCo2() > $maxCo2) {
            $severity = $reading->getCo2() > ($maxCo2 + 500) ? 'critical' : 'warning';
            $alerts[] = $this->createAlert(
                device: $device,
                reading: $reading,
                type: 'high_co2',
                severity: $severity,
                message: sprintf(
                    'High CO₂ level detected: %d ppm (threshold: %d ppm)',
                    $reading->getCo2(),
                    $maxCo2
                ),
                value: (float) $reading->getCo2(),
                threshold: (float) $maxCo2
            );
        }

        if ($reading->getNoise() > $maxNoise) {
            $severity = $reading->getNoise() > ($maxNoise + 10) ? 'critical' : 'warning';
            $alerts[] = $this->createAlert(
                device: $device,
                reading: $reading,
                type: 'high_noise',
                severity: $severity,
                message: sprintf(
                    'High noise level detected: %.1f dB (threshold: %.1f dB)',
                    $reading->getNoise(),
                    $maxNoise
                ),
                value: $reading->getNoise(),
                threshold: $maxNoise
            );
        }

        if (!empty($alerts)) {
        $this->entityManager->flush();
        
        foreach ($alerts as $alert) {
            $this->notificationService->notifyAlert($alert);
        }
}

        return $alerts;
    }

    private function createAlert(
        Device $device,
        SensorReading $reading,
        string $type,
        string $severity,
        string $message,
        float $value,
        float $threshold
    ): Alert {
        $alert = new Alert();
        $alert->setUser($device->getUser());
        $alert->setDevice($device);
        $alert->setSensorReading($reading);
        $alert->setType($type);
        $alert->setSeverity($severity);
        $alert->setMessage($message);
        $alert->setValue($value);
        $alert->setThreshold($threshold);

        $this->entityManager->persist($alert);

        $this->logger->info('Alert created', [
            'type' => $type,
            'severity' => $severity,
            'device' => $device->getName(),
            'value' => $value,
            'threshold' => $threshold
        ]);

        return $alert;
    }

    public function getUnacknowledgedAlerts($user, int $limit = 10): array
    {
        return $this->entityManager->getRepository(Alert::class)->findBy(
            ['user' => $user, 'isAcknowledged' => false],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    public function acknowledgeAlert(Alert $alert): void
    {
        $alert->setIsAcknowledged(true);
        $alert->setAcknowledgedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
}