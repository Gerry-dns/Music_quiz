<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: \App\Repository\ArtistRepository::class)]
#[ORM\Table(name: 'artist')]
class Artist
{
     #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    private ?string $mbid = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $albums = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private array $members = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $subGenres = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $beginArea = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $lifeSpan = [];
    

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $youtubeUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $wikidataUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $spotifyUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $deezerUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $bandcampUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $discogsUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $officialSiteUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $soundcloudUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lastfmUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $twitterUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $facebookUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $instagramUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $disambiguation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: true)]
    private ?Country $country = null;

    #[ORM\ManyToOne(targetEntity: Genre::class)]
    #[ORM\JoinColumn(name: 'main_genre_id', referencedColumnName: 'id', nullable: true)]
    private ?Genre $mainGenre = null;

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

    public function getMembers(): array
    {
        return $this->members;
    }

    public function setMembers(array $members): self
    {
        $this->members = $members;
        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $bio): self
    {
        $this->biography = $bio;
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

    public function getYoutubeUrl(): ?string { return $this->youtubeUrl; }
    public function setYoutubeUrl(?string $url): self { $this->youtubeUrl = $url; return $this; }

    public function getWikidataUrl(): ?string { return $this->wikidataUrl; }
    public function setWikidataUrl(?string $url): self { $this->wikidataUrl = $url; return $this; }

    public function getSpotifyUrl(): ?string { return $this->spotifyUrl; }
    public function setSpotifyUrl(?string $url): self { $this->spotifyUrl = $url; return $this; }

    public function getDeezerUrl(): ?string { return $this->deezerUrl; }
    public function setDeezerUrl(?string $url): self { $this->deezerUrl = $url; return $this; }

    public function getBandcampUrl(): ?string { return $this->bandcampUrl; }
    public function setBandcampUrl(?string $url): self { $this->bandcampUrl = $url; return $this; }

    public function getDiscogsUrl(): ?string { return $this->discogsUrl; }
    public function setDiscogsUrl(?string $url): self { $this->discogsUrl = $url; return $this; }

    public function getOfficialSiteUrl(): ?string { return $this->officialSiteUrl; }
    public function setOfficialSiteUrl(?string $url): self { $this->officialSiteUrl = $url; return $this; }

    public function getSoundcloudUrl(): ?string { return $this->soundcloudUrl; }
    public function setSoundcloudUrl(?string $url): self { $this->soundcloudUrl = $url; return $this; }

    public function getLastfmUrl(): ?string { return $this->lastfmUrl; }
    public function setLastfmUrl(?string $url): self { $this->lastfmUrl = $url; return $this; }

    public function getTwitterUrl(): ?string { return $this->twitterUrl; }
    public function setTwitterUrl(?string $url): self { $this->twitterUrl = $url; return $this; }

    public function getFacebookUrl(): ?string { return $this->facebookUrl; }
    public function setFacebookUrl(?string $url): self { $this->facebookUrl = $url; return $this; }

    public function getInstagramUrl(): ?string { return $this->instagramUrl; }
    public function setInstagramUrl(?string $url): self { $this->instagramUrl = $url; return $this; }

    public function getDisambiguation(): ?string { return $this->disambiguation; }
    public function setDisambiguation(?string $text): self { $this->disambiguation = $text; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): self { $this->type = $type; return $this; }

    public function getCountry(): ?Country { return $this->country; }
    public function setCountry(?Country $country): self { $this->country = $country; return $this; }

    public function getMainGenre(): ?Genre { return $this->mainGenre; }
    public function setMainGenre(?Genre $genre): self { $this->mainGenre = $genre; return $this; }

    public function __toString(): string
{
    return $this->name ?? '';
}

}



