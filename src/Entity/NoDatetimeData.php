<?php

namespace App\Entity;

use App\Repository\NoDatetimeDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NoDatetimeDataRepository::class)
 */
class NoDatetimeData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $l12G;

    /**
     * @ORM\Column(type="float")
     */
    private $l13G;

    /**
     * @ORM\Column(type="float")
     */
    private $l23G;

    /**
     * @ORM\Column(type="float")
     */
    private $l1N;

    /**
     * @ORM\Column(type="float")
     */
    private $l2N;

    /**
     * @ORM\Column(type="float")
     */
    private $l3N;

    /**
     * @ORM\Column(type="float")
     */
    private $l12M;

    /**
     * @ORM\Column(type="float")
     */
    private $l13M;

    /**
     * @ORM\Column(type="float")
     */
    private $l23M;

    /**
     * @ORM\Column(type="float")
     */
    private $i1;

    /**
     * @ORM\Column(type="float")
     */
    private $i2;

    /**
     * @ORM\Column(type="float")
     */
    private $i3;

    /**
     * @ORM\Column(type="float")
     */
    private $freq;

    /**
     * @ORM\Column(type="float")
     */
    private $iDiff;

    /**
     * @ORM\Column(type="integer")
     */
    private $fuelLevel;

    /**
     * @ORM\Column(type="integer")
     */
    private $waterLevel;

    /**
     * @ORM\Column(type="integer")
     */
    private $oilLevel;

    /**
     * @ORM\Column(type="float")
     */
    private $airPressure;

    /**
     * @ORM\Column(type="float")
     */
    private $oilPressure;

    /**
     * @ORM\Column(type="float")
     */
    private $waterTemperature;

    /**
     * @ORM\Column(type="float")
     */
    private $coolerTemperature;

    /**
     * @ORM\Column(type="integer")
     */
    private $engineSpeed;

    /**
     * @ORM\Column(type="float")
     */
    private $battVoltage;

    /**
     * @ORM\Column(type="integer")
     */
    private $hoursToMaintenance;

    /**
     * @ORM\Column(type="integer")
     */
    private $gensetRunning;

    /**
     * @ORM\Column(type="integer")
     */
    private $cg;

    /**
     * @ORM\Column(type="integer")
     */
    private $mainsPresence;

    /**
     * @ORM\Column(type="integer")
     */
    private $cr;

    /**
     * @ORM\Column(type="integer")
     */
    private $maintenanceRequest;

    /**
     * @ORM\Column(type="integer")
     */
    private $lowFuel;

    /**
     * @ORM\Column(type="integer")
     */
    private $presenceWaterInFuel;

    /**
     * @ORM\Column(type="integer")
     */
    private $overspeed;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxFreq;

    /**
     * @ORM\Column(type="integer")
     */
    private $minFreq;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxVolt;

    /**
     * @ORM\Column(type="integer")
     */
    private $minVolt;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxBattVolt;

    /**
     * @ORM\Column(type="integer")
     */
    private $minBattVolt;

    /**
     * @ORM\Column(type="integer")
     */
    private $overload;

    /**
     * @ORM\Column(type="integer")
     */
    private $shortCircuit;

    /**
     * @ORM\Column(type="integer")
     */
    private $mainsIncSeq;

    /**
     * @ORM\Column(type="integer")
     */
    private $gensetIncSeq;

    /**
     * @ORM\Column(type="integer")
     */
    private $differentialIntervention;

    /**
     * @ORM\OneToOne(targetEntity=SmartMod::class, inversedBy="noDatetimeData", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $smartMod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getL12G(): ?float
    {
        return $this->l12G;
    }

    public function setL12G(float $l12G): self
    {
        $this->l12G = $l12G;

        return $this;
    }

    public function getL13G(): ?float
    {
        return $this->l13G;
    }

    public function setL13G(float $l13G): self
    {
        $this->l13G = $l13G;

        return $this;
    }

    public function getL23G(): ?float
    {
        return $this->l23G;
    }

    public function setL23G(float $l23G): self
    {
        $this->l23G = $l23G;

        return $this;
    }

    public function getL1N(): ?float
    {
        return $this->l1N;
    }

    public function setL1N(float $l1N): self
    {
        $this->l1N = $l1N;

        return $this;
    }

    public function getL2N(): ?float
    {
        return $this->l2N;
    }

    public function setL2N(float $l2N): self
    {
        $this->l2N = $l2N;

        return $this;
    }

    public function getL3N(): ?float
    {
        return $this->l3N;
    }

    public function setL3N(float $l3N): self
    {
        $this->l3N = $l3N;

        return $this;
    }

    public function getL12M(): ?float
    {
        return $this->l12M;
    }

    public function setL12M(float $l12M): self
    {
        $this->l12M = $l12M;

        return $this;
    }

    public function getL13M(): ?float
    {
        return $this->l13M;
    }

    public function setL13M(float $l13M): self
    {
        $this->l13M = $l13M;

        return $this;
    }

    public function getL23M(): ?float
    {
        return $this->l23M;
    }

    public function setL23M(float $l23M): self
    {
        $this->l23M = $l23M;

        return $this;
    }

    public function getI1(): ?float
    {
        return $this->i1;
    }

    public function setI1(float $i1): self
    {
        $this->i1 = $i1;

        return $this;
    }

    public function getI2(): ?float
    {
        return $this->i2;
    }

    public function setI2(float $i2): self
    {
        $this->i2 = $i2;

        return $this;
    }

    public function getI3(): ?float
    {
        return $this->i3;
    }

    public function setI3(float $i3): self
    {
        $this->i3 = $i3;

        return $this;
    }

    public function getFreq(): ?float
    {
        return $this->freq;
    }

    public function setFreq(float $freq): self
    {
        $this->freq = $freq;

        return $this;
    }

    public function getIDiff(): ?float
    {
        return $this->iDiff;
    }

    public function setIDiff(float $iDiff): self
    {
        $this->iDiff = $iDiff;

        return $this;
    }

    public function getFuelLevel(): ?int
    {
        return $this->fuelLevel;
    }

    public function setFuelLevel(int $fuelLevel): self
    {
        $this->fuelLevel = $fuelLevel;

        return $this;
    }

    public function getWaterLevel(): ?int
    {
        return $this->waterLevel;
    }

    public function setWaterLevel(int $waterLevel): self
    {
        $this->waterLevel = $waterLevel;

        return $this;
    }

    public function getOilLevel(): ?int
    {
        return $this->oilLevel;
    }

    public function setOilLevel(int $oilLevel): self
    {
        $this->oilLevel = $oilLevel;

        return $this;
    }

    public function getAirPressure(): ?float
    {
        return $this->airPressure;
    }

    public function setAirPressure(float $airPressure): self
    {
        $this->airPressure = $airPressure;

        return $this;
    }

    public function getOilPressure(): ?float
    {
        return $this->oilPressure;
    }

    public function setOilPressure(float $oilPressure): self
    {
        $this->oilPressure = $oilPressure;

        return $this;
    }

    public function getWaterTemperature(): ?float
    {
        return $this->waterTemperature;
    }

    public function setWaterTemperature(float $waterTemperature): self
    {
        $this->waterTemperature = $waterTemperature;

        return $this;
    }

    public function getCoolerTemperature(): ?float
    {
        return $this->coolerTemperature;
    }

    public function setCoolerTemperature(float $coolerTemperature): self
    {
        $this->coolerTemperature = $coolerTemperature;

        return $this;
    }

    public function getEngineSpeed(): ?int
    {
        return $this->engineSpeed;
    }

    public function setEngineSpeed(int $engineSpeed): self
    {
        $this->engineSpeed = $engineSpeed;

        return $this;
    }

    public function getBattVoltage(): ?float
    {
        return $this->battVoltage;
    }

    public function setBattVoltage(float $battVoltage): self
    {
        $this->battVoltage = $battVoltage;

        return $this;
    }

    public function getHoursToMaintenance(): ?int
    {
        return $this->hoursToMaintenance;
    }

    public function setHoursToMaintenance(int $hoursToMaintenance): self
    {
        $this->hoursToMaintenance = $hoursToMaintenance;

        return $this;
    }

    public function getGensetRunning(): ?int
    {
        return $this->gensetRunning;
    }

    public function setGensetRunning(int $gensetRunning): self
    {
        $this->gensetRunning = $gensetRunning;

        return $this;
    }

    public function getCg(): ?int
    {
        return $this->cg;
    }

    public function setCg(int $cg): self
    {
        $this->cg = $cg;

        return $this;
    }

    public function getMainsPresence(): ?int
    {
        return $this->mainsPresence;
    }

    public function setMainsPresence(int $mainsPresence): self
    {
        $this->mainsPresence = $mainsPresence;

        return $this;
    }

    public function getCr(): ?int
    {
        return $this->cr;
    }

    public function setCr(int $cr): self
    {
        $this->cr = $cr;

        return $this;
    }

    public function getMaintenanceRequest(): ?int
    {
        return $this->maintenanceRequest;
    }

    public function setMaintenanceRequest(int $maintenanceRequest): self
    {
        $this->maintenanceRequest = $maintenanceRequest;

        return $this;
    }

    public function getLowFuel(): ?int
    {
        return $this->lowFuel;
    }

    public function setLowFuel(int $lowFuel): self
    {
        $this->lowFuel = $lowFuel;

        return $this;
    }

    public function getPresenceWaterInFuel(): ?int
    {
        return $this->presenceWaterInFuel;
    }

    public function setPresenceWaterInFuel(int $presenceWaterInFuel): self
    {
        $this->presenceWaterInFuel = $presenceWaterInFuel;

        return $this;
    }

    public function getOverspeed(): ?int
    {
        return $this->overspeed;
    }

    public function setOverspeed(int $overspeed): self
    {
        $this->overspeed = $overspeed;

        return $this;
    }

    public function getMaxFreq(): ?int
    {
        return $this->maxFreq;
    }

    public function setMaxFreq(int $maxFreq): self
    {
        $this->maxFreq = $maxFreq;

        return $this;
    }

    public function getMinFreq(): ?int
    {
        return $this->minFreq;
    }

    public function setMinFreq(int $minFreq): self
    {
        $this->minFreq = $minFreq;

        return $this;
    }

    public function getMaxVolt(): ?int
    {
        return $this->maxVolt;
    }

    public function setMaxVolt(int $maxVolt): self
    {
        $this->maxVolt = $maxVolt;

        return $this;
    }

    public function getMinVolt(): ?int
    {
        return $this->minVolt;
    }

    public function setMinVolt(int $minVolt): self
    {
        $this->minVolt = $minVolt;

        return $this;
    }

    public function getMaxBattVolt(): ?int
    {
        return $this->maxBattVolt;
    }

    public function setMaxBattVolt(int $maxBattVolt): self
    {
        $this->maxBattVolt = $maxBattVolt;

        return $this;
    }

    public function getMinBattVolt(): ?int
    {
        return $this->minBattVolt;
    }

    public function setMinBattVolt(int $minBattVolt): self
    {
        $this->minBattVolt = $minBattVolt;

        return $this;
    }

    public function getOverload(): ?int
    {
        return $this->overload;
    }

    public function setOverload(int $overload): self
    {
        $this->overload = $overload;

        return $this;
    }

    public function getShortCircuit(): ?int
    {
        return $this->shortCircuit;
    }

    public function setShortCircuit(int $shortCircuit): self
    {
        $this->shortCircuit = $shortCircuit;

        return $this;
    }

    public function getMainsIncSeq(): ?int
    {
        return $this->mainsIncSeq;
    }

    public function setMainsIncSeq(int $mainsIncSeq): self
    {
        $this->mainsIncSeq = $mainsIncSeq;

        return $this;
    }

    public function getGensetIncSeq(): ?int
    {
        return $this->gensetIncSeq;
    }

    public function setGensetIncSeq(int $gensetIncSeq): self
    {
        $this->gensetIncSeq = $gensetIncSeq;

        return $this;
    }

    public function getDifferentialIntervention(): ?int
    {
        return $this->differentialIntervention;
    }

    public function setDifferentialIntervention(int $differentialIntervention): self
    {
        $this->differentialIntervention = $differentialIntervention;

        return $this;
    }

    public function getSmartMod(): ?SmartMod
    {
        return $this->smartMod;
    }

    public function setSmartMod(SmartMod $smartMod): self
    {
        $this->smartMod = $smartMod;

        return $this;
    }
}
