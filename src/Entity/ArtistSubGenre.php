<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ArtistSubGenreRepository::class)]
class ArtistSubGenre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Artist::class, inversedBy: 'artistSubGenres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artist $artist = null;

    #[ORM\ManyToOne(targetEntity: Genre::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Genre $genre = null;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $count = 1;

    public function getId(): ?int { return $this->id; }
    public function getArtist(): ?Artist { return $this->artist; }
    public function setArtist(?Artist $artist): self { $this->artist = $artist; return $this; }
    public function getGenre(): ?Genre { return $this->genre; }
    public function setGenre(?Genre $genre): self { $this->genre = $genre; return $this; }
    public function getCount(): int { return $this->count; }
    public function setCount(int $count): self { $this->count = $count; return $this; }
}
