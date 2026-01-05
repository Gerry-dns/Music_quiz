<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: \App\Repository\ArtistMemberRepository::class)]
class ArtistMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // Relation vers Artist
    #[ORM\ManyToOne(targetEntity: Artist::class, inversedBy: 'artistMembers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artist $artist = null;

    // Relation vers Member
    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'artistMemberships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $member = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $begin = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $end = null;


    #[ORM\Column(type: 'boolean')]
    private bool $isOriginal = false;

    // Instruments joués dans ce groupe
    #[ORM\OneToMany(mappedBy: 'artistMember', targetEntity: ArtistMemberInstrument::class, cascade: ['persist', 'remove'])]
    private Collection $memberInstruments;

    public function __construct()
    {
        $this->memberInstruments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): self
    {
        $this->artist = $artist;
        return $this;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;
        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function getBegin(): ?\DateTimeInterface
    {
        return $this->begin;
    }

    public function setBegin(?\DateTimeInterface $begin): self
    {
        $this->begin = $begin;
        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;
        return $this;
    }

    public function getIsOriginal(): bool
    {
        return $this->isOriginal;
    }

    public function setIsOriginal(bool $isOriginal): self
    {
        $this->isOriginal = $isOriginal;
        return $this;
    }

    /**
     * @return Collection|ArtistMemberInstrument[]
     */
    public function getMemberInstruments(): Collection
    {
        return $this->memberInstruments;
    }

    public function addMemberInstrument(ArtistMemberInstrument $instrument): self
    {
        if (!$this->memberInstruments->contains($instrument)) {
            $this->memberInstruments->add($instrument);
            $instrument->setArtistMember($this);
        }
        return $this;
    }

    public function removeMemberInstrument(ArtistMemberInstrument $instrument): self
    {
        if ($this->memberInstruments->removeElement($instrument)) {
            if ($instrument->getArtistMember() === $this) {
                $instrument->setArtistMember(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        // On retourne le nom du membre + éventuellement son rôle ou instrument principal
        return $this->getMember()?->getName() ?? 'Membre inconnu';
    }

    public function hasInstrument(string $instrumentName): bool
    {
        foreach ($this->getMemberInstruments() as $memberInstrument) {
            $instr = $memberInstrument->getInstrument()?->getName();
            if ($instr && strtolower($instr) === strtolower($instrumentName)) {
                return true;
            }
        }
        return false;
    }
}
