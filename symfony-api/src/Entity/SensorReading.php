<?php

namespace App\Entity;

use App\Repository\SensorReadingRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: SensorReadingRepository::class)]
class SensorReading
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[ORM\Column(length: 255)]
    #[ORM\Column(length: 255, name: 'device_identifier')]
    private ?string $deviceId = null;

    #[ORM\Column]
    private ?float $temperature = null;

    #[ORM\Column]
    private ?float $humidity = null;

    #[ORM\Column]
    private ?int $co2 = null;

    #[ORM\Column]
    private ?float $noise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $timestamp = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sensorReadings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'sensorReadings')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Device $device = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): static
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getHumidity(): ?float
    {
        return $this->humidity;
    }

    public function setHumidity(float $humidity): static
    {
        $this->humidity = $humidity;

        return $this;
    }

    public function getCo2(): ?int
    {
        return $this->co2;
    }

    public function setCo2(int $co2): static
    {
        $this->co2 = $co2;

        return $this;
    }

    public function getNoise(): ?float
    {
        return $this->noise;
    }

    public function setNoise(float $noise): static
    {
        $this->noise = $noise;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): static
    {
        $this->device = $device;
        return $this;
    }
}