<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(targetEntity: SensorReading::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $sensorReadings;

    public function __construct()
    {
        $this->sensorReadings = new ArrayCollection();
        $this->devices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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
            $sensorReading->setUser($this);
        }
        return $this;
    }

    public function removeSensorReading(SensorReading $sensorReading): static
    {
        if ($this->sensorReadings->removeElement($sensorReading)) {
            if ($sensorReading->getUser() === $this) {
                $sensorReading->setUser(null);
            }
        }
        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }


    #[ORM\OneToMany(targetEntity: Device::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $devices;

    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(Device $device): static
    {
        if (!$this->devices->contains($device)) {
            $this->devices->add($device);
            $device->setUser($this);
        }
        return $this;
    }

    public function removeDevice(Device $device): static
    {
        if ($this->devices->removeElement($device)) {
            if ($device->getUser() === $this) {
                $device->setUser(null);
            }
        }
        return $this;
    }
}
