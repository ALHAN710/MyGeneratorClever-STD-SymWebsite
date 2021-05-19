<?php

namespace App\Entity;

use DateTime;
use DateTimeZone;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SiteRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=SiteRepository::class)
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(
 * fields={"name","enterprise"},
 * message="Un autre Site possède déjà ce nom, merci de le modifier"
 * )
 */
class Site
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="Nom du Site obligatoire")
     * @Assert\Length(min=3, minMessage="Le Nom du Site doit contenir au moins 3 caractères !", maxMessage="Le nom du Site doit contenir au max 50 caractères !")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $slug;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank(message="Veuillez renseigner la puissance souscrite du Site")
     */
    private $powerSubscribed;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="sites")
     */
    private $users;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fuelPrice;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $currency;

    /**
     * @ORM\OneToMany(targetEntity=SmartMod::class, mappedBy="site", orphanRemoval=true)
     */
    private $smartMods;

    /**
     * @ORM\ManyToOne(targetEntity=Enterprise::class, inversedBy="sites")
     * @ORM\JoinColumn(nullable=false)
     */
    private $enterprise;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Permet d'initialiser le slug !
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeSlug()
    {

        $slugify = new Slugify();
        $this->slug = $slugify->slugify($this->name);
    }

    /**
     * Permet d'initialiser la date de création de l'utilisateur
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeCreatedAt()
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
        }
    }

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPowerSubscribed(): ?float
    {
        return $this->powerSubscribed;
    }

    public function setPowerSubscribed(float $powerSubscribed): self
    {
        $this->powerSubscribed = $powerSubscribed;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

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

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

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
            $smartMod->setSite($this);
        }

        return $this;
    }

    public function removeSmartMod(SmartMod $smartMod): self
    {
        if ($this->smartMods->removeElement($smartMod)) {
            // set the owning side to null (unless already changed)
            if ($smartMod->getSite() === $this) {
                $smartMod->setSite(null);
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
