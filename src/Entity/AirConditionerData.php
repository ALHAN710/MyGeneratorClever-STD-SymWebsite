<?php

namespace App\Entity;

use App\Repository\AirConditionerDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AirConditionerDataRepository::class)
 */
class AirConditionerData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $returnAirTemp;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $returnAirHum;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fanSpeed1;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateTime;

    /**
     * @ORM\ManyToOne(targetEntity=SmartMod::class, inversedBy="airConditionerData")
     */
    private $smartMod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReturnAirTemp(): ?float
    {
        return $this->returnAirTemp;
    }

    public function setReturnAirTemp(?float $returnAirTemp): self
    {
        $this->returnAirTemp = $returnAirTemp;

        return $this;
    }

    public function getReturnAirHum(): ?float
    {
        return $this->returnAirHum;
    }

    public function setReturnAirHum(?float $returnAirHum): self
    {
        $this->returnAirHum = $returnAirHum;

        return $this;
    }

    public function getFanSpeed1(): ?float
    {
        return $this->fanSpeed1;
    }

    public function setFanSpeed1(?float $fanSpeed1): self
    {
        $this->fanSpeed1 = $fanSpeed1;

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

    public function getSmartMod(): ?SmartMod
    {
        return $this->smartMod;
    }

    public function setSmartMod(?SmartMod $smartMod): self
    {
        $this->smartMod = $smartMod;

        return $this;
    }
}
