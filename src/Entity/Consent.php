<?php

namespace App\Entity;

use App\Repository\ConsentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConsentRepository::class)
 */
class Consent
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
    private $topic;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_create;

    /**
     * @ORM\OneToMany(targetEntity=DriverConsent::class, mappedBy="consent")
     */
    private $driverConsents;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enable;

    public function __construct()
    {
        $this->driverConsents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDateCreate(): ?\DateTimeInterface
    {
        return $this->date_create;
    }

    public function setDateCreate(\DateTimeInterface $date_create): self
    {
        $this->date_create = $date_create;

        return $this;
    }

    /**
     * @return Collection<int, DriverConsent>
     */
    public function getDriverConsents(): Collection
    {
        return $this->driverConsents;
    }

    public function addDriverConsent(DriverConsent $driverConsent): self
    {
        if (!$this->driverConsents->contains($driverConsent)) {
            $this->driverConsents[] = $driverConsent;
            $driverConsent->setConsent($this);
        }

        return $this;
    }

    public function removeDriverConsent(DriverConsent $driverConsent): self
    {
        if ($this->driverConsents->removeElement($driverConsent)) {
            // set the owning side to null (unless already changed)
            if ($driverConsent->getConsent() === $this) {
                $driverConsent->setConsent(null);
            }
        }

        return $this;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;

        return $this;
    }
}
