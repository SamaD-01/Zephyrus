<?php

namespace App\Service;

use App\Entity\Alert;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NotificationService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $discordWebhookUrl
    ) {}

    public function sendDiscordNotification(Alert $alert): void
    {
        if (empty($this->discordWebhookUrl) || $this->discordWebhookUrl === 'https://discord.com/api/webhooks/CHANGE_ME') {
            $this->logger->info('Discord webhook not configured, skipping notification');
            return;
        }

        try {
            $color = $this->getSeverityColor($alert->getSeverity());
            $emoji = $alert->getSeverityIcon();
            
            $embed = [
                'embeds' => [
                    [
                        'title' => "{$emoji} {$this->getAlertTitle($alert->getType())}",
                        'description' => $alert->getMessage(),
                        'color' => $color,
                        'fields' => [
                            [
                                'name' => '📍 Device',
                                'value' => $alert->getDevice()->getName(),
                                'inline' => true
                            ],
                            [
                                'name' => '🏠 Location',
                                'value' => $alert->getDevice()->getLocation() ?? 'Not set',
                                'inline' => true
                            ],
                            [
                                'name' => '📊 Current Value',
                                'value' => $this->formatValue($alert->getType(), $alert->getValue()),
                                'inline' => true
                            ],
                            [
                                'name' => '⚠️ Threshold',
                                'value' => $this->formatValue($alert->getType(), $alert->getThreshold()),
                                'inline' => true
                            ],
                            [
                                'name' => '🔔 Severity',
                                'value' => strtoupper($alert->getSeverity()),
                                'inline' => true
                            ],
                            [
                                'name' => '🕐 Time',
                                'value' => $alert->getCreatedAt()->format('Y-m-d H:i:s'),
                                'inline' => true
                            ]
                        ],
                        'footer' => [
                            'text' => 'Zephyrus IoT Monitoring System'
                        ],
                        'timestamp' => $alert->getCreatedAt()->format('c')
                    ]
                ]
            ];

            $response = $this->httpClient->request('POST', $this->discordWebhookUrl, [
                'json' => $embed,
                'timeout' => 5
            ]);

            if ($response->getStatusCode() === 204) {
                $this->logger->info('Discord notification sent successfully', [
                    'alert_id' => $alert->getId(),
                    'type' => $alert->getType()
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to send Discord notification', [
                'error' => $e->getMessage(),
                'alert_id' => $alert->getId()
            ]);
        }
    }

    public function sendEmailNotification(Alert $alert): void
    {
        $this->logger->info('Email notification not yet implemented');
    }

    public function notifyAlert(Alert $alert): void
    {
        $this->sendDiscordNotification($alert);
        $this->sendEmailNotification($alert);
    }

    private function getSeverityColor(string $severity): int
    {
        return match($severity) {
            'critical' => 15548997, // Red
            'warning' => 16776960,  // Yellow
            'info' => 3447003,      // Blue
            default => 9807270      // Gray
        };
    }

    private function getAlertTitle(string $type): string
    {
        return match($type) {
            'high_temperature' => 'High Temperature Alert',
            'low_temperature' => 'Low Temperature Alert',
            'high_co2' => 'High CO₂ Alert',
            'high_noise' => 'High Noise Alert',
            default => 'System Alert'
        };
    }

    private function formatValue(string $type, float $value): string
    {
        return match($type) {
            'high_temperature', 'low_temperature' => number_format($value, 1) . '°C',
            'high_co2' => number_format($value, 0) . ' ppm',
            'high_noise' => number_format($value, 1) . ' dB',
            default => (string) $value
        };
    }
}