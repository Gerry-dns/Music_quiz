<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Artist;

#[ORM\Entity]
class Instrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private  $id = null;

    #[ORM\Column(type: "string", length: 100, unique: true)]
    private $name;
    /**
     * @var Collection<int, ArtistInstrument>
     */
    #[ORM\OneToMany(targetEntity: ArtistInstrument::class, mappedBy: 'instrument')]
    private Collection $artistInstruments;

    /**
     * Instruments joués par les membres dans un groupe (ArtistMemberInstrument)
     * @var Collection<int, ArtistMemberInstrument>
     */
    #[ORM\OneToMany(targetEntity: ArtistMemberInstrument::class, mappedBy: 'instrument', cascade: ['persist', 'remove'])]
    private Collection $artistMemberInstruments;

    public function __construct()
    {
        $this->artistInstruments = new ArrayCollection();
        $this->artistMemberInstruments = new ArrayCollection();
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

    /**
     * @return Collection<int, ArtistInstrument>
     */
    public function getArtistInstruments(): Collection
    {
        return $this->artistInstruments;
    }

    public function addArtistInstrument(ArtistInstrument $artistInstrument): static
    {
        if (!$this->artistInstruments->contains($artistInstrument)) {
            $this->artistInstruments->add($artistInstrument);
            $artistInstrument->setInstrument($this);
        }

        return $this;
    }

    public function removeArtistInstrument(ArtistInstrument $artistInstrument): static
    {
        if ($this->artistInstruments->removeElement($artistInstrument)) {
            // set the owning side to null (unless already changed)
            if ($artistInstrument->getInstrument() === $this) {
                $artistInstrument->setInstrument(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ArtistMemberInstrument>
     */
    public function getArtistMemberInstruments(): Collection
    {
        return $this->artistMemberInstruments;
    }

    public function addArtistMemberInstrument(ArtistMemberInstrument $ami): self
    {
        if (!$this->artistMemberInstruments->contains($ami)) {
            $this->artistMemberInstruments->add($ami);
            $ami->setInstrument($this);
        }
        return $this;
    }

    public function removeArtistMemberInstrument(ArtistMemberInstrument $ami): self
    {
        $this->artistMemberInstruments->removeElement($ami);
        return $this;
    }

    public function __toString(): string
    {
        return $this->getName() ?? 'Instrument inconnu';
    }
}
