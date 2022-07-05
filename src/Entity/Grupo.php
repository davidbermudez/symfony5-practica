<?php

namespace App\Entity;

use App\Repository\GrupoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GrupoRepository::class)
 */
class Grupo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $caption;

    /**
     * @ORM\OneToMany(targetEntity=Driver::class, mappedBy="grupo", orphanRemoval=true)
     */
    private $driver;

    /**
     * @ORM\OneToMany(targetEntity=Fecha::class, mappedBy="grupo", orphanRemoval=true)
     */
    private $fecha;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $chatid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $chatlink;

    public function __construct()
    {
        $this->admin = new ArrayCollection();
        $this->driver = new ArrayCollection();
    }

    public function __toString()
    {        
        return $this->caption;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    
    /**
     * @return Collection<int, Driver>
     */
    public function getDriver(): Collection
    {
        return $this->driver;
    }

    public function addDriver(Driver $driver): self
    {
        if (!$this->driver->contains($driver)) {
            $this->driver[] = $driver;
            $driver->setGrupo($this);
        }

        return $this;
    }

    public function removeDriver(Driver $driver): self
    {
        if ($this->driver->removeElement($driver)) {
            // set the owning side to null (unless already changed)
            if ($driver->getGrupo() === $this) {
                $driver->setGrupo(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getChatid(): ?string
    {
        return $this->chatid;
    }

    public function setChatid(?string $chatid): self
    {
        $this->chatid = $chatid;

        return $this;
    }

    public function getChatlink(): ?string
    {
        return $this->chatlink;
    }

    public function setChatlink(?string $chatlink): self
    {
        $this->chatlink = $chatlink;

        return $this;
    }
}
