<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $isoCode = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $flag = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $continent = null;

    /**
     * @var Collection<int, City>
     */
    #[ORM\OneToMany(mappedBy: 'country', targetEntity: City::class, cascade: ['persist', 'remove'])]
    private Collection $cities;

    #[ORM\OneToMany(mappedBy: 'country', targetEntity: Artist::class, cascade: ['persist', 'remove'])]
    private Collection $artists;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->artists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIsoCode(): ?string
    {
        return $this->isoCode;
    }

    public function setIsoCode(?string $isoCode): static
    {
        $this->isoCode = $isoCode;

        return $this;
    }

    public function getFlag(): ?string
    {
        return $this->flag;
    }

    public function setFlag(?string $flag): static
    {
        $this->flag = $flag;

        return $this;
    }

    public function getContinent(): ?string
    {
        return $this->continent;
    }

    public function setContinent(?string $continent): static
    {
        $this->continent = $continent;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->setCountry($this);
        }

        return $this;
    }

    public function removeCity(City $city): static
    {
        if ($this->cities->removeElement($city)) {
            if ($city->getCountry() === $this) {
                $city->setCountry(null);
            }
        }

        return $this;
    }

    public function getArtists(): Collection
    {
        return $this->artists;
    }

    // Ajouter un artiste
    public function addArtist(Artist $artist): static
    {
        if (!$this->artists->contains($artist)) {
            $this->artists->add($artist);
            $artist->setCountry($this);
        }

        return $this;
    }

    // Supprimer un artiste
    public function removeArtist(Artist $artist): static
    {
        if ($this->artists->removeElement($artist)) {
            if ($artist->getCountry() === $this) {
                $artist->setCountry(null);
            }
        }

        return $this;
    }
}
