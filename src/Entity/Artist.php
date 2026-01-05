<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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

    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: Album::class, cascade: ['persist', 'remove'])]
    private Collection $albums;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?String $summary = null;

    #[ORM\ManyToOne(targetEntity: Country::class, inversedBy: 'artists')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    #[ORM\ManyToOne(targetEntity: City::class, inversedBy: 'artists')]
    #[ORM\JoinColumn(nullable: true)]
    private ?City $beginArea = null;

    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: ArtistMember::class, cascade: ['persist', 'remove'])]
    private Collection $artistMembers;

    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: ArtistInstrument::class, cascade: ['persist', 'remove'])]
    private Collection $artistInstruments;

    #[ORM\ManyToMany(targetEntity: Decade::class, inversedBy: 'artists')]
    #[ORM\JoinTable(name: "artist_decade")]
    private Collection $decades;

    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: ArtistSubGenre::class, cascade: ['persist', 'remove'])]
    private Collection $artistSubGenres;


    #[ORM\ManyToOne(targetEntity: Genre::class, inversedBy: 'artists')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Genre $mainGenre = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $urls = [];

    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: Questions::class, cascade: ['persist', 'remove'])]
    private Collection $questions;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $beginDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;


    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->albums = new ArrayCollection();
        $this->artistMembers = new ArrayCollection();
        $this->artistInstruments = new ArrayCollection();
        $this->decades = new ArrayCollection();
        $this->artistSubGenres = new ArrayCollection();
        $this->decades = new ArrayCollection();
    }

    // GETTERS / SETTERS

    public function getUrls(): array
    {
        return $this->urls;
    }
    public function setUrls(array $urls): self
    {
        $this->urls = $urls;
        return $this;
    }

    public function getMembers(): array
    {
        return $this->artistMembers->toArray();
    }

    public function getMainGenre(): ?Genre
    {
        return $this->mainGenre;
    }

    public function setMainGenre(?Genre $mainGenre): self
    {
        $this->mainGenre = $mainGenre;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(?string $name): self
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

    public function getAlbums(): Collection
    {
        return $this->albums;
    }

    public function addAlbum(Album $album): self
    {
        if (!$this->albums->contains($album)) {
            $this->albums->add($album);
            $album->setArtist($this);
        }
        return $this;
    }

    public function removeAlbum(Album $album): self
    {
        if ($this->albums->removeElement($album)) {
            $album->setArtist(null);
        }
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



    public function getSummary(): ?string

    {
        return $this->summary;
    }
    public function setSummary(?string  $summary): self
    {
        $this->summary = $summary;
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

    public function getBeginArea(): ?City
    {
        return $this->beginArea;
    }
    public function setBeginArea(?City $beginArea): self
    {
        $this->beginArea = $beginArea;
        return $this;
    }

    public function getArtistMembers(): Collection
    {
        return $this->artistMembers;
    }
    public function addArtistMember(ArtistMember $am): self
    {
        if (!$this->artistMembers->contains($am)) {
            $this->artistMembers->add($am);
            $am->setArtist($this);
        }
        return $this;
    }
    public function removeArtistMember(ArtistMember $am): self
    {
        if ($this->artistMembers->removeElement($am)) {
            if ($am->getArtist() === $this) $am->setArtist(null);
        }
        return $this;
    }

    public function getArtistInstruments(): Collection
    {
        return $this->artistInstruments;
    }
    public function addArtistInstrument(ArtistInstrument $ai): self
    {
        if (!$this->artistInstruments->contains($ai)) {
            $this->artistInstruments->add($ai);
            $ai->setArtist($this);
        }
        return $this;
    }
    public function removeArtistInstrument(ArtistInstrument $ai): self
    {
        if ($this->artistInstruments->removeElement($ai)) {
            if ($ai->getArtist() === $this) $ai->setArtist(null);
        }
        return $this;
    }

    public function getDecades(): Collection
    {
        return $this->decades;
    }
    public function addDecade(Decade $decade): self
    {
        if (!$this->decades->contains($decade)) $this->decades->add($decade);
        return $this;
    }
    public function removeDecade(Decade $decade): self
    {
        $this->decades->removeElement($decade);
        return $this;
    }

    public function getArtistSubGenres(): Collection
    {
        return $this->artistSubGenres;
    }
    public function addArtistSubGenre(ArtistSubGenre $asg): self
    {
        if (!$this->artistSubGenres->contains($asg)) {
            $this->artistSubGenres->add($asg);
            $asg->setArtist($this);
        }
        return $this;
    }
    public function removeArtistSubGenre(ArtistSubGenre $asg): self
    {
        if ($this->artistSubGenres->removeElement($asg)) $asg->setArtist(null);
        return $this;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getBeginDate(): ?\DateTimeInterface
    {
        return $this->beginDate;
    }
    public function setBeginDate(?\DateTimeInterface $beginDate): self
    {
        $this->beginDate = $beginDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getInstrumentsAsString(): string
    {
        return implode(', ', $this->artistInstruments->map(fn($ai) => $ai->getInstrument()->getName())->toArray());
    }
  public function getLifeSpanAsArray(): array
{
    $items = [];

    if ($this->beginDate) {
        $items[] = 'Début : '.$this->beginDate->format('Y-m-d');
    }

    if ($this->endDate) {
        $items[] = 'Fin : '.$this->endDate->format('Y-m-d');
    }

    $items[] = $this->isActive ? 'Actif' : 'Inactif';

    return $items;
}
  public function getUrlsForAdmin(): array
{
    $result = [];

    if (empty($this->urls) || !is_array($this->urls)) {
        return $result;
    }

    foreach ($this->urls as $label => $value) {

        // cas : string simple (wikidata)
        if (is_string($value)) {
            $result[] = [
                'label' => $label,
                'url' => $value,
            ];
            continue;
        }

        // cas : tableau de strings
        if (is_array($value) && isset($value[0]) && is_string($value[0])) {
            foreach ($value as $url) {
                $result[] = [
                    'label' => $label,
                    'url' => $url,
                ];
            }
            continue;
        }

        // cas : objet imbriqué (social)
        if (is_array($value)) {
            foreach ($value as $subLabel => $urls) {
                if (is_array($urls)) {
                    foreach ($urls as $url) {
                        $result[] = [
                            'label' => $label . ' / ' . $subLabel,
                            'url' => $url,
                        ];
                    }
                }
            }
        }
    }

    return $result;
}

    public function getMembersAsArray(): array
    {
        $result = [];
        foreach ($this->artistMembers as $artistMember) {
            $result[] = [
                'name' => $artistMember->getMember()->getName(),
                'instruments' => array_map(
                    fn($i) => $i->getInstrument()->getName(),
                    $artistMember->getMemberInstruments()->toArray()
                ),
                'begin' => $artistMember->getBegin()?->format('Y-m-d'),
                'end'   => $artistMember->getEnd()?->format('Y-m-d'),
            ];
        }
        return $result;
    }
    public function getArtistSubGenresAsString(): string
    {
        return implode(', ', $this->artistSubGenres->map(fn($sg) => $sg->getGenre()->getName())->toArray());
    }
}
