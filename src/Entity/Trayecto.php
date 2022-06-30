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
     * @ORM\ManyToOne(targetEntity=Driver::class, inversedBy="trayectos")
     * @ORM\JoinColumn(nullable=false)     
     */
    private $driver;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $passenger;

    /**
     * @ORM\ManyToOne(targetEntity=Fecha::class, inversedBy="trayectos", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $fecha;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFecha(): ?Fecha
    {
        return $this->fecha;
    }

    public function setFecha(?Fecha $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }


}
