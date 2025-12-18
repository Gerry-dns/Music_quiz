<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Decade;
use App\Entity\Instrument;

#[ORM\Entity(repositoryClass: \App\Repository\ArtistRepository::class)]
#[ORM\Table(name: 'artist')]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    private ?string $mbid = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $albums = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $tracks = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $members = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $biography = [];


    #[ORM\Column(type: 'json', nullable: true)]
    private array $subGenres = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $beginArea = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $lifeSpan = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $urls = [];


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $disambiguation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: true)]
    private ?Country $country = null;

    #[ORM\ManyToOne(targetEntity: Genre::class, inversedBy: 'artists')]
    #[ORM\JoinColumn(name: 'main_genre_id', referencedColumnName: 'id', nullable: true)]
    private ?Genre $mainGenre = null;

    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: Questions::class)]
    private Collection $questions;

    #[ORM\ManyToMany(targetEntity: Decade::class, inversedBy: 'artists')]
    #[ORM\JoinTable(name: 'artist_decade')]
    private Collection $decades;

    #[ORM\ManyToMany(targetEntity: Instrument::class, inversedBy: 'artists')]
    #[ORM\JoinTable(name: 'artist_instrument')]
    private Collection $instruments;


    public function __construct()
    {
        $this->decades = new ArrayCollection();
        $this->instruments = new ArrayCollection();
        $this->questions = new ArrayCollection();
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    #[ORM\PostLoad]
    public function initUrls(): void
    {
        if ($this->urls === null) {
            $this->urls = [];
        }
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

    public function getMbid(): ?string
    {
        return $this->mbid;
    }

    public function setMbid(?string $mbid): self
    {
        $this->mbid = $mbid;
        return $this;
    }


    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): self
    {
        $this->coverImage = $coverImage;
        return $this;
    }

    public function getAlbums(): array
    {
        return $this->albums;
    }

    public function setAlbums(array $albums): self
    {
        $this->albums = $albums;
        return $this;
    }

    public function getTracks(): array
    {
        return $this->tracks;
    }

    public function setTracks(array $tracks): self
    {
        $this->tracks = $tracks;
        return $this;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function setMembers(array $members): self
    {
        $this->members = $members;
        return $this;
    }

    public function getBiography(): array
    {
        return $this->biography ?? [];
    }


    public function setBiography(?array $biography): self
    {
        $this->biography = $biography;
        return $this;
    }

    public function getSubGenres(): array
    {
        return $this->subGenres;
    }

    public function setSubGenres(array $subGenres): self
    {
        $this->subGenres = $subGenres;
        return $this;
    }

    public function getBeginArea(): ?string
    {
        return $this->beginArea;
    }

    public function setBeginArea(?string $area): self
    {
        $this->beginArea = $area;
        return $this;
    }

    public function getLifeSpan(): ?array
    {
        return $this->lifeSpan;
    }

    public function setLifespan(?array $lifeSpan): self
    {
        $this->lifeSpan = $lifeSpan;
        return $this;
    }



    public function getDisambiguation(): ?string
    {
        return $this->disambiguation;
    }
    public function setDisambiguation(?string $text): self
    {
        $this->disambiguation = $text;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }
    public function setCountry(?Country $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getMainGenre(): ?Genre
    {
        return $this->mainGenre;
    }
    public function setMainGenre(?Genre $genre): self
    {
        $this->mainGenre = $genre;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
    public function getUrls(): array
    {
        return $this->urls ?? [];
    }

    public function setUrls(array $urls): self
    {
        $this->urls = $urls;
        return $this;
    }

    /**
     * @return Collection<int, Decade>
     */
    public function getDecades(): Collection
    {
        return $this->decades;
    }

    public function addDecade(Decade $decade): self
    {
        if (!$this->decades->contains($decade)) {
            $this->decades->add($decade);
        }

        return $this;
    }

    public function removeDecade(Decade $decade): self
    {
        $this->decades->removeElement($decade);

        return $this;
    }

    /**
     * @return Collection<int, Instrument>
     */
    public function getInstruments(): Collection
    {
        return $this->instruments;
    }

    public function addInstrument(Instrument $instrument): self
    {
        if (!$this->instruments->contains($instrument)) {
            $this->instruments->add($instrument);
        }

        return $this;
    }

    public function removeInstrument(Instrument $instrument): self
    {
        $this->instruments->removeElement($instrument);

        return $this;
    }

    public function getBeginYear(): ?string
    {
        return $this->lifeSpan['begin'] ?? null;
    }

    public function getEndDate(): ?string
    {
        return $this->lifeSpan['end'] ?? null;
    }
}
