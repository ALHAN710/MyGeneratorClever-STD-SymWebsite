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
     * @ORM\JoinColumn(nullable=false)
     */
    private $site;

    /**
     * @ORM\OneToMany(targetEntity=DataMod::class, mappedBy="smartMod")
     */
    private $dataMods;

    public function __construct()
    {
        $this->dataMods = new ArrayCollection();
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
     * @return Collection|DataMod[]
     */
    public function getDataMods(): Collection
    {
        return $this->dataMods;
    }

    public function addDataMod(DataMod $dataMod): self
    {
        if (!$this->dataMods->contains($dataMod)) {
            $this->dataMods[] = $dataMod;
            $dataMod->setSmartMod($this);
        }

        return $this;
    }

    public function removeDataMod(DataMod $dataMod): self
    {
        if ($this->dataMods->removeElement($dataMod)) {
            // set the owning side to null (unless already changed)
            if ($dataMod->getSmartMod() === $this) {
                $dataMod->setSmartMod(null);
            }
        }

        return $this;
    }
}
