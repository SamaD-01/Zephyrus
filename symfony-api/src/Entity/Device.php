<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $deviceId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'devices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: SensorReading::class, mappedBy: 'device')]
    private Collection $sensorReadings;

    #[ORM\Column(nullable: true)]
    private ?float $maxTemperature = null;

    #[ORM\Column(nullable: true)]
    private ?float $minTemperature = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxCo2 = null;

    #[ORM\Column(nullable: true)]
    private ?float $maxNoise = null;

    #[ORM\OneToMany(targetEntity: Alert::class, mappedBy: 'device', orphanRemoval: true)]
    private Collection $alerts;


    public function __construct()
    {
        $this->sensorReadings = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->isActive = true;
        $this->alerts = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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





    public function getSensorReadings(): Collection
    {
        return $this->sensorReadings;
    }

    public function addSensorReading(SensorReading $sensorReading): static
    {
        if (!$this->sensorReadings->contains($sensorReading)) {
            $this->sensorReadings->add($sensorReading);
            $sensorReading->setDevice($this);
        }
        return $this;
    }

    public function removeSensorReading(SensorReading $sensorReading): static
    {
        if ($this->sensorReadings->removeElement($sensorReading)) {
            if ($sensorReading->getDevice() === $this) {
                $sensorReading->setDevice(null);
            }
        }
        return $this;
    }
    
    public function getMaxTemperature(): ?float
    {
        return $this->maxTemperature;
    }

    public function setMaxTemperature(?float $maxTemperature): static
    {
        $this->maxTemperature = $maxTemperature;
        return $this;
    }

    public function getMinTemperature(): ?float
    {
        return $this->minTemperature;
    }

    public function setMinTemperature(?float $minTemperature): static
    {
        $this->minTemperature = $minTemperature;
        return $this;
    }

    public function getMaxCo2(): ?int
    {
        return $this->maxCo2;
    }

    public function setMaxCo2(?int $maxCo2): static
    {
        $this->maxCo2 = $maxCo2;
        return $this;
    }

    public function getMaxNoise(): ?float
    {
        return $this->maxNoise;
    }

    public function setMaxNoise(?float $maxNoise): static
    {
        $this->maxNoise = $maxNoise;
        return $this;
    }
    
    public function __toString(): string
    {
        return $this->name ?? $this->deviceId ?? '';
    }

    
    
    public function getAlerts(): Collection
    {
        return $this->alerts;
    }

    public function addAlert(Alert $alert): static
    {
        if (!$this->alerts->contains($alert)) {
            $this->alerts->add($alert);
            $alert->setDevice($this);
        }
        return $this;
    }

    public function removeAlert(Alert $alert): static
    {
        if ($this->alerts->removeElement($alert)) {
            if ($alert->getDevice() === $this) {
                $alert->setDevice(null);
            }
        }
        return $this;
    }
}
