<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZoneRepository::class)
 */
class Zone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="zones")
     * @ORM\JoinColumn(nullable=false)
     */
    private $site;

    /**
     * @ORM\ManyToMany(targetEntity=SmartMod::class, inversedBy="zones")
     */
    private $smartMods;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $powerSubscribed;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $type;

    public function __construct()
    {
        $this->smartMods = new ArrayCollection();
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
     * @return Collection|SmartMod[]
     */
    public function getSmartMods(): Collection
    {
        return $this->smartMods;
    }

    public function addSmartMod(SmartMod $smartMod): self
    {
        if (!$this->smartMods->contains($smartMod)) {
            $this->smartMods[] = $smartMod;
        }

        return $this;
    }

    public function removeSmartMod(SmartMod $smartMod): self
    {
        $this->smartMods->removeElement($smartMod);

        return $this;
    }

    public function getPowerSubscribed(): ?float
    {
        return $this->powerSubscribed;
    }

    public function setPowerSubscribed(?float $powerSubscribed): self
    {
        $this->powerSubscribed = $powerSubscribed;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
