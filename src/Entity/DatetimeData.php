<?php

namespace App\Entity;

use App\Repository\DatetimeDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DatetimeDataRepository::class)
 */
class DatetimeData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=SmartMod::class, inversedBy="datetimeData")
     * @ORM\JoinColumn(nullable=false)
     */
    private $smartMod;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $p;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $q;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $s;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $cosfi;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $totalRunningHours;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fuelInstConsumption;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalEnergy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbPerformedStartUps;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbMainsInterruption;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateTime;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fuelLevel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSmartMod(): ?SmartMod
    {
        return $this->smartMod;
    }

    public function setSmartMod(?SmartMod $smartMod): self
    {
        $this->smartMod = $smartMod;

        return $this;
    }

    public function getP(): ?float
    {
        return $this->p;
    }

    public function setP(float $p): self
    {
        $this->p = $p;

        return $this;
    }

    public function getQ(): ?float
    {
        return $this->q;
    }

    public function setQ(float $q): self
    {
        $this->q = $q;

        return $this;
    }

    public function getS(): ?float
    {
        return $this->s;
    }

    public function setS(float $s): self
    {
        $this->s = $s;

        return $this;
    }

    public function getCosfi(): ?float
    {
        return $this->cosfi;
    }

    public function setCosfi(float $cosfi): self
    {
        $this->cosfi = $cosfi;

        return $this;
    }

    public function getTotalRunningHours(): ?float
    {
        return $this->totalRunningHours;
    }

    public function setTotalRunningHours(float $totalRunningHours): self
    {
        $this->totalRunningHours = $totalRunningHours;

        return $this;
    }

    public function getFuelInstConsumption(): ?float
    {
        return $this->fuelInstConsumption;
    }

    public function setFuelInstConsumption(float $fuelInstConsumption): self
    {
        $this->fuelInstConsumption = $fuelInstConsumption;

        return $this;
    }

    public function getTotalEnergy(): ?int
    {
        return $this->totalEnergy;
    }

    public function setTotalEnergy(int $totalEnergy): self
    {
        $this->totalEnergy = $totalEnergy;

        return $this;
    }

    public function getNbPerformedStartUps(): ?int
    {
        return $this->nbPerformedStartUps;
    }

    public function setNbPerformedStartUps(int $nbPerformedStartUps): self
    {
        $this->nbPerformedStartUps = $nbPerformedStartUps;

        return $this;
    }

    public function getNbMainsInterruption(): ?int
    {
        return $this->nbMainsInterruption;
    }

    public function setNbMainsInterruption(int $nbMainsInterruption): self
    {
        $this->nbMainsInterruption = $nbMainsInterruption;

        return $this;
    }

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeInterface $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getFuelLevel(): ?float
    {
        return $this->fuelLevel;
    }

    public function setFuelLevel(?float $fuelLevel): self
    {
        $this->fuelLevel = $fuelLevel;

        return $this;
    }
}
