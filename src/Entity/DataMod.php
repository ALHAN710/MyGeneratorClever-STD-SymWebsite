<?php

namespace App\Entity;

use App\Repository\DataModRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DataModRepository::class)
 */
class DataMod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=SmartMod::class, inversedBy="dataMods")
     * @ORM\JoinColumn(nullable=false)
     */
    private $smartMod;

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
}
