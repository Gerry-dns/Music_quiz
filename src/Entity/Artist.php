<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 36, nullable: true)]
    private ?string $mbid = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $mainGenre = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $subGenres = [];

    #[ORM\Column(nullable: true)]
    private ?int $foundedYear = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $albums = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $members = [];

    /**
     * @var Collection<int, Questions>
     */
    #[ORM\OneToMany(targetEntity: Questions::class, mappedBy: 'artist')]
    private Collection $questions;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biography = null;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    // --- Getters et Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getMbid(): ?string
    {
        return $this->mbid;
    }
    public function setMbid(?string $mbid): static
    {
        $this->mbid = $mbid;
        return $this;
    }

    public function getMainGenre(): ?string
    {
        return $this->mainGenre;
    }
    public function setMainGenre(?string $mainGenre): static
    {
        $this->mainGenre = $mainGenre;
        return $this;
    }

    public function getSubGenres(): array
    {
        return $this->subGenres ?? [];
    }
    public function setSubGenres(array $subGenres): static
    {
        $this->subGenres = $subGenres;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }
    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getFoundedYear(): ?int
    {
        return $this->foundedYear;
    }
    public function setFoundedYear(?int $foundedYear): static
    {
        $this->foundedYear = $foundedYear;
        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }
    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;
        return $this;
    }

    public function getAlbums(): array
    {
        return $this->albums ?? [];
    }
    public function setAlbums(array $albums): static
    {
        $this->albums = array_map('strval', $albums);
        return $this;
    }

    public function getMembers(): array
    {
        return $this->members ?? [];
    }
    public function setMembers(array $members): static
    {
        $this->members = $members;
        return $this;
    }

    /**
     * @return Collection<int, Questions>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Questions $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setArtist($this);
        }
        return $this;
    }

    public function removeQuestion(Questions $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getArtist() === $this) {
                $question->setArtist(null);
            }
        }
        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }
    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;
        return $this;
    }
}
