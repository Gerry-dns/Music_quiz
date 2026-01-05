<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\MemberRepository;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    // Instruments joués par ce membre
    #[ORM\OneToMany(mappedBy: 'member', targetEntity: MemberInstrument::class, cascade: ['persist', 'remove'])]
    private Collection $memberInstruments;

    // Groupes/artistes auxquels le membre appartient
    #[ORM\OneToMany(mappedBy: 'member', targetEntity: ArtistMember::class, cascade: ['persist', 'remove'])]
    private Collection $artistMemberships;

    public function __construct()
    {
        $this->memberInstruments = new ArrayCollection();
        $this->artistMemberships = new ArrayCollection();
    }

    // ---------- Getters & Setters ----------

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
     * @return Collection<int, MemberInstrument>
     */
    public function getMemberInstruments(): Collection
    {
        return $this->memberInstruments;
    }

    public function addMemberInstrument(MemberInstrument $mi): self
    {
        if (!$this->memberInstruments->contains($mi)) {
            $this->memberInstruments->add($mi);
            $mi->setMember($this);
        }
        return $this;
    }

    public function removeMemberInstrument(MemberInstrument $mi): self
    {
        if ($this->memberInstruments->removeElement($mi)) {
            if ($mi->getMember() === $this) {
                $mi->setMember(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ArtistMember>
     */
    public function getArtistMemberships(): Collection
    {
        return $this->artistMemberships;
    }

    public function addArtistMembership(ArtistMember $am): self
    {
        if (!$this->artistMemberships->contains($am)) {
            $this->artistMemberships->add($am);
            $am->setMember($this);
        }
        return $this;
    }

    public function removeArtistMembership(ArtistMember $am): self
    {
        if ($this->artistMemberships->removeElement($am)) {
            if ($am->getMember() === $this) {
                $am->setMember(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->getName() ?? ''; // Retourne le nom du membre pour l'affichage
    }
}
