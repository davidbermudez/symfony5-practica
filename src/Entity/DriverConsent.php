<?php

namespace App\Entity;

use App\Repository\DriverConsentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DriverConsentRepository::class)
 */
class DriverConsent
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Driver::class, inversedBy="driverConsents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $driver;

    /**
     * @ORM\ManyToOne(targetEntity=Consent::class, inversedBy="driverConsents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $consent;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_consent;

    /**
     * @ORM\Column(type="boolean")
     */
    private $choice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getConsent(): ?Consent
    {
        return $this->consent;
    }

    public function setConsent(?Consent $consent): self
    {
        $this->consent = $consent;

        return $this;
    }

    public function getDateConsent(): ?\DateTimeInterface
    {
        return $this->date_consent;
    }

    public function setDateConsent(\DateTimeInterface $date_consent): self
    {
        $this->date_consent = $date_consent;

        return $this;
    }

    public function isChoice(): ?bool
    {
        return $this->choice;
    }

    public function setChoice(bool $choice): self
    {
        $this->choice = $choice;

        return $this;
    }
}
