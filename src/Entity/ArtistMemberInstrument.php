<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ArtistMemberInstrumentRepository::class)]
class ArtistMemberInstrument
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ArtistMember::class, inversedBy: 'memberInstruments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ArtistMember $artistMember = null;

    #[ORM\ManyToOne(targetEntity: Instrument::class, inversedBy: 'artistMemberInstruments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Instrument $instrument = null;

   #[ORM\Column(name: 'is_primary', type: 'boolean')]
private bool $primary = false;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getArtistMember(): ArtistMember
    {
        return $this->artistMember ;
    }
    public function setArtistMember(?ArtistMember $artistMember): self
    {
        $this->artistMember = $artistMember;
        return $this;
    }
    public function getInstrument(): Instrument
    {
        return $this->instrument;
    }
    public function setInstrument(Instrument $instrument): self
    {
        $this->instrument = $instrument;
        return $this;
    }
    public function isPrimary(): bool
    {
        return $this->primary;
    }
    public function setPrimary(bool $primary): self
    {
        $this->primary = $primary;
        return $this;
    }
}
