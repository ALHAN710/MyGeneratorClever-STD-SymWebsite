<?php

namespace App\Entity;

use App\Repository\TarificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TarificationRepository::class)
 */
class Tarification
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
    private $tva;

    /**
     * @ORM\Column(type="float")
     */
    private $primeFixe;

    /**
     * @ORM\Column(type="float")
     */
    private $tarifAcFuelHp;

    /**
     * @ORM\Column(type="float")
     */
    private $tarifAcFuelP;

    /**
     * @ORM\Column(type="float")
     */
    private $tarifAcGridHp;

    /**
     * @ORM\Column(type="float")
     */
    private $tarifAcGridP;

    /**
     * @ORM\Column(type="float")
     */
    private $tarifDcHp;

    /**
     * @ORM\Column(type="float")
     */
    private $tarifDcP;

    /**
     * @ORM\OneToOne(targetEntity=Site::class, inversedBy="tarification", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $site;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(float $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

    public function getPrimeFixe(): ?float
    {
        return $this->primeFixe;
    }

    public function setPrimeFixe(float $primeFixe): self
    {
        $this->primeFixe = $primeFixe;

        return $this;
    }

    public function getTarifAcFuelHp(): ?float
    {
        return $this->tarifAcFuelHp;
    }

    public function setTarifAcFuelHp(float $tarifAcFuelHp): self
    {
        $this->tarifAcFuelHp = $tarifAcFuelHp;

        return $this;
    }

    public function getTarifAcFuelP(): ?float
    {
        return $this->tarifAcFuelP;
    }

    public function setTarifAcFuelP(float $tarifAcFuelP): self
    {
        $this->tarifAcFuelP = $tarifAcFuelP;

        return $this;
    }

    public function getTarifAcGridHp(): ?float
    {
        return $this->tarifAcGridHp;
    }

    public function setTarifAcGridHp(float $tarifAcGridHp): self
    {
        $this->tarifAcGridHp = $tarifAcGridHp;

        return $this;
    }

    public function getTarifAcGridP(): ?float
    {
        return $this->tarifAcGridP;
    }

    public function setTarifAcGridP(float $tarifAcGridP): self
    {
        $this->tarifAcGridP = $tarifAcGridP;

        return $this;
    }

    public function getTarifDcHp(): ?float
    {
        return $this->tarifDcHp;
    }

    public function setTarifDcHp(float $tarifDcHp): self
    {
        $this->tarifDcHp = $tarifDcHp;

        return $this;
    }

    public function getTarifDcP(): ?float
    {
        return $this->tarifDcP;
    }

    public function setTarifDcP(float $tarifDcP): self
    {
        $this->tarifDcP = $tarifDcP;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(Site $site): self
    {
        $this->site = $site;

        return $this;
    }
}
