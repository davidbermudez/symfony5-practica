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
     * @ORM\OneToMany(targetEntity=Admin::class, mappedBy="grupo", orphanRemoval=true)
     */
    private $admin;

    /**
     * @ORM\OneToMany(targetEntity=Driver::class, mappedBy="grupo", orphanRemoval=true)
     */
    private $driver;

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
     * @return Collection<int, Admin>
     */
    public function getAdmin(): Collection
    {
        return $this->admin;
    }

    public function addAdmin(Admin $admin): self
    {
        if (!$this->admin->contains($admin)) {
            $this->admin[] = $admin;
            $admin->setGrupo($this);
        }

        return $this;
    }

    public function removeAdmin(Admin $admin): self
    {
        if ($this->admin->removeElement($admin)) {
            // set the owning side to null (unless already changed)
            if ($admin->getGrupo() === $this) {
                $admin->setGrupo(null);
            }
        }

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
}
