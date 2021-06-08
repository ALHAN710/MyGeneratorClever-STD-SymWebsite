<?php

namespace App\Entity;

use App\Repository\AlarmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AlarmRepository::class)
 */
class Alarm
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $label;

    /**
     * @ORM\OneToMany(targetEntity=AlarmReporting::class, mappedBy="alarm")
     */
    private $alarmReportings;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $type;

    public function __construct()
    {
        $this->alarmReportings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

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
            $alarmReporting->setAlarm($this);
        }

        return $this;
    }

    public function removeAlarmReporting(AlarmReporting $alarmReporting): self
    {
        if ($this->alarmReportings->removeElement($alarmReporting)) {
            // set the owning side to null (unless already changed)
            if ($alarmReporting->getAlarm() === $this) {
                $alarmReporting->setAlarm(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
