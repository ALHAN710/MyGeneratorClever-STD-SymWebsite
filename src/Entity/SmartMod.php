<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SmartModRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=SmartModRepository::class)
 * @UniqueEntity(
 * fields={"moduleId"},
 * message="Un autre module possède déjà cet identifiant, merci de le modifier"
 * )
 */
class SmartMod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="Veuillez donner le nom de la zone couverte par le module")
     * @Assert\Length(min=3, minMessage="Le nom doit contenir au moins 3 caractères !", maxMessage="Le nom doit contenir au max 50 caractères !")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank(message="Veuillez entrer l'identifiant unique du module")
     * @Assert\Length(min=6, minMessage="L'identifiant doit contenir au moins 6 caractères !", maxMessage="L'identifiant doit contenir au max 20 caractères !")
     */
    private $moduleId;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $modType;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="smartMods")
     * @ORM\JoinColumn(nullable=true)
     */
    private $site;

    /**
     * @ORM\OneToMany(targetEntity=DatetimeData::class, mappedBy="smartMod")
     */
    private $datetimeData;

    /**
     * @ORM\OneToOne(targetEntity=NoDatetimeData::class, mappedBy="smartMod", cascade={"persist", "remove"})
     */
    private $noDatetimeData;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fuelPrice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $levelZone;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbPhases;

    /**
     * @ORM\ManyToMany(targetEntity=Zone::class, mappedBy="smartMods")
     */
    private $zones;

    /**
     * @ORM\OneToMany(targetEntity=LoadDataEnergy::class, mappedBy="smartMod", orphanRemoval=true)
     */
    private $loadDataEnergies;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $subType;

    /**
     * @ORM\OneToMany(targetEntity=AlarmReporting::class, mappedBy="smartMod")
     */
    private $alarmReportings;

    /**
     * @ORM\ManyToOne(targetEntity=Enterprise::class, inversedBy="smartMods")
     * @ORM\JoinColumn(nullable=false)
     */
    private $enterprise;

    private $modName;
    private $smartModName;

    /**
     * @ORM\OneToMany(targetEntity=ClimateData::class, mappedBy="smartMod", orphanRemoval=true)
     */
    private $climateData;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isPublic;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $power;

    public function __construct()
    {
        $this->datetimeData = new ArrayCollection();
        $this->zones = new ArrayCollection();
        $this->loadDataEnergies = new ArrayCollection();
        $this->alarmReportings = new ArrayCollection();
        $this->climateData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getModuleId(): ?string
    {
        return $this->moduleId;
    }

    public function setModuleId(string $moduleId): self
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    public function getModType(): ?string
    {
        return $this->modType;
    }

    public function setModType(string $modType): self
    {
        $this->modType = $modType;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection|DatetimeData[]
     */
    public function getDatetimeData(): Collection
    {
        return $this->datetimeData;
    }

    public function addDatetimeData(DatetimeData $datetimeData): self
    {
        if (!$this->datetimeData->contains($datetimeData)) {
            $this->datetimeData[] = $datetimeData;
            $datetimeData->setSmartMod($this);
        }

        return $this;
    }

    public function removeDatetimeData(DatetimeData $datetimeData): self
    {
        if ($this->datetimeData->removeElement($datetimeData)) {
            // set the owning side to null (unless already changed)
            if ($datetimeData->getSmartMod() === $this) {
                $datetimeData->setSmartMod(null);
            }
        }

        return $this;
    }

    public function getNoDatetimeData(): ?NoDatetimeData
    {
        return $this->noDatetimeData;
    }

    public function setNoDatetimeData(NoDatetimeData $noDatetimeData): self
    {
        // set the owning side of the relation if necessary
        if ($noDatetimeData->getSmartMod() !== $this) {
            $noDatetimeData->setSmartMod($this);
        }

        $this->noDatetimeData = $noDatetimeData;

        return $this;
    }

    public function getFuelPrice(): ?float
    {
        return $this->fuelPrice;
    }

    public function setFuelPrice(?float $fuelPrice): self
    {
        $this->fuelPrice = $fuelPrice;

        return $this;
    }

    public function getLevelZone(): ?int
    {
        return $this->levelZone;
    }

    public function setLevelZone(?int $levelZone): self
    {
        $this->levelZone = $levelZone;

        return $this;
    }

    public function getNbPhases(): ?int
    {
        return $this->nbPhases;
    }

    public function setNbPhases(?int $nbPhases): self
    {
        $this->nbPhases = $nbPhases;

        return $this;
    }

    /**
     * @return Collection|Zone[]
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function addZone(Zone $zone): self
    {
        if (!$this->zones->contains($zone)) {
            $this->zones[] = $zone;
            $zone->addSmartMod($this);
        }

        return $this;
    }

    public function removeZone(Zone $zone): self
    {
        if ($this->zones->removeElement($zone)) {
            $zone->removeSmartMod($this);
        }

        return $this;
    }

    /**
     * @return Collection|LoadDataEnergy[]
     */
    public function getLoadDataEnergies(): Collection
    {
        return $this->loadDataEnergies;
    }

    public function addLoadDataEnergy(LoadDataEnergy $loadDataEnergy): self
    {
        if (!$this->loadDataEnergies->contains($loadDataEnergy)) {
            $this->loadDataEnergies[] = $loadDataEnergy;
            $loadDataEnergy->setSmartMod($this);
        }

        return $this;
    }

    public function removeLoadDataEnergy(LoadDataEnergy $loadDataEnergy): self
    {
        if ($this->loadDataEnergies->removeElement($loadDataEnergy)) {
            // set the owning side to null (unless already changed)
            if ($loadDataEnergy->getSmartMod() === $this) {
                $loadDataEnergy->setSmartMod(null);
            }
        }

        return $this;
    }

    public function getSubType(): ?string
    {
        return $this->subType;
    }

    public function setSubType(?string $subType): self
    {
        $this->subType = $subType;

        return $this;
    }

    /**
     * @return Collection|AlarmReporting[]
     */
    public function getAlarmReportings(): Collection
    {
        return $this->alarmReportings;
    }

    public function addAlarmReporting(AlarmReporting $alarmReporting): self
    {
        if (!$this->alarmReportings->contains($alarmReporting)) {
            $this->alarmReportings[] = $alarmReporting;
            $alarmReporting->setSmartMod($this);
        }

        return $this;
    }

    public function removeAlarmReporting(AlarmReporting $alarmReporting): self
    {
        if ($this->alarmReportings->removeElement($alarmReporting)) {
            // set the owning side to null (unless already changed)
            if ($alarmReporting->getSmartMod() === $this) {
                $alarmReporting->setSmartMod(null);
            }
        }

        return $this;
    }

    public function getEnterprise(): ?Enterprise
    {
        return $this->enterprise;
    }

    public function setEnterprise(?Enterprise $enterprise): self
    {
        $this->enterprise = $enterprise;

        return $this;
    }

    public function getModName(): ?String
    {
        return $this->getName();
    }

    public function setModName(String $modName): self
    {
        $this->modName = $modName;

        return $this;
    }
    public function getSmartModName(): ?SmartMod
    {
        return $this->smartModName;
    }

    public function setSmartModName(SmartMod $smartModName): self
    {
        $this->smartModName = $smartModName;

        return $this;
    }

    /**
     * @return Collection|ClimateData[]
     */
    public function getClimateData(): Collection
    {
        return $this->climateData;
    }

    public function addClimateData(ClimateData $climateData): self
    {
        if (!$this->climateData->contains($climateData)) {
            $this->climateData[] = $climateData;
            $climateData->setSmartMod($this);
        }

        return $this;
    }

    public function removeClimateData(ClimateData $climateData): self
    {
        if ($this->climateData->removeElement($climateData)) {
            // set the owning side to null (unless already changed)
            if ($climateData->getSmartMod() === $this) {
                $climateData->setSmartMod(null);
            }
        }

        return $this;
    }

    public function getIsPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(?bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getPower(): ?float
    {
        return $this->power;
    }

    public function setPower(?float $power): self
    {
        $this->power = $power;

        return $this;
    }
}
