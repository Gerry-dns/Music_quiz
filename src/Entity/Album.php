<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Artist;

#[ORM\Entity]
#[ORM\Table(name: 'album')]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    private ?string $mbid = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $firstReleaseDate = null;

    #[ORM\ManyToOne(targetEntity: Artist::class, inversedBy: 'albums')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artist $artist = null;

    // GETTERS / SETTERS
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getMbid(): ?string
    {
        return $this->mbid;
    }
    public function setMbid(?string $mbid): self
    {
        $this->mbid = $mbid;
        return $this;
    }

    public function getFirstReleaseDate(): ?\DateTimeInterface
    {
        return $this->firstReleaseDate;
    }
    public function setFirstReleaseDate(?\DateTimeInterface $date): self
    {
        $this->firstReleaseDate = $date;
        return $this;
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
}
