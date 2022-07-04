<?php

namespace App\Entity;

use App\Repository\FechaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FechaRepository::class)
 */
class Fecha
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
     * @ORM\ManyToOne(targetEntity=Grupo::class, inversedBy="fecha")
     * @ORM\JoinColumn(nullable=false)
     */
    private $grupo;

    /**
     * @ORM\OneToMany(targetEntity=Trayecto::class, mappedBy="fecha", orphanRemoval=true)
     */
    private $trayectos;

    /**
     * @ORM\OneToMany(targetEntity=Definitivo::class, mappedBy="fecha", orphanRemoval=true)
     */
    private $definitivos;


    public function __construct()
    {
        $this->trayectos = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Trayecto>
     */
    public function getTrayectos(): Collection
    {
        return $this->trayectos;
    }

    public function addTrayecto(Trayecto $trayecto): self
    {
        if (!$this->trayectos->contains($trayecto)) {
            $this->trayectos[] = $trayecto;
            $trayecto->setFecha($this);
        }

        return $this;
    }

    public function removeTrayecto(Trayecto $trayecto): self
    {
        if ($this->trayectos->removeElement($trayecto)) {
            // set the owning side to null (unless already changed)
            if ($trayecto->getFecha() === $this) {
                $trayecto->setFecha(null);
            }
        }

        return $this;
    }

    public function getGrupo(): ?Grupo
    {
        return $this->grupo;
    }

    public function setGrupo(?Grupo $grupo): self
    {
        $this->grupo = $grupo;

        return $this;
    }
}
