<?php

namespace App\Entity;

use App\Repository\TrayectoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrayectoRepository::class)
 */
class Trayecto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date_trayecto;

    /**
     * @ORM\Column(type="time")
     */
    private $time_at;

    /**
     * @ORM\Column(type="time")
     */
    private $time_to;

    /**
     * @ORM\ManyToOne(targetEntity=Driver::class, inversedBy="trayectos")
     * @ORM\JoinColumn(nullable=false)     
     */
    private $driver;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $passenger;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTrayecto(): ?\DateTimeInterface
    {
        return $this->date_trayecto;
    }

    public function setDateTrayecto(\DateTimeInterface $date_trayecto): self
    {
        $this->date_trayecto = $date_trayecto;

        return $this;
    }

    public function getTimeAt(): ?\DateTimeInterface
    {
        return $this->time_at;
    }

    public function setTimeAt(\DateTimeInterface $time_at): self
    {
        $this->time_at = $time_at;

        return $this;
    }

    public function getTimeTo(): ?\DateTimeInterface
    {
        return $this->time_to;
    }

    public function setTimeTo(\DateTimeInterface $time_to): self
    {
        $this->time_to = $time_to;

        return $this;
    }

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function isPassenger(): ?bool
    {
        return $this->passenger;
    }

    public function setPassenger(?bool $passenger): self
    {
        $this->passenger = $passenger;

        return $this;
    }


}
