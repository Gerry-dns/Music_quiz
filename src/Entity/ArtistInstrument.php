<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ArtistInstrumentRepository::class)]
class ArtistInstrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Artist::class, inversedBy: 'artistInstruments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artist $artist = null;

    #[ORM\ManyToOne(targetEntity: Instrument::class, inversedBy: 'artistInstruments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Instrument $instrument = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $count = 1;

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
    public function getInstrument(): ?Instrument
    {
        return $this->instrument;
    }
    public function setInstrument(?Instrument $instrument): self
    {
        $this->instrument = $instrument;
        return $this;
    }
    public function getCount(): ?int
    {
        return $this->count;
    }
    public function setCount(?int $count): self
    {
        $this->count = $count;
        return $this;
    }
}
