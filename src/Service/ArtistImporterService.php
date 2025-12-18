<?php

namespace App\Service;

use App\Entity\Artist;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtistImporterService
{
    public function __construct(private HttpClientInterface $client) {}

    /**
     * Importe un artiste depuis MusicBrainz via son MBID
     */
    public function importFromMBID(string $mbid): Artist
    {
        $response = $this->client->request('GET', "https://musicbrainz.org/ws/2/artist/{$mbid}", [
            'headers' => [
                'User-Agent' => 'MyApp/1.0',
                'Accept' => 'application/json'
            ],
            'query' => [
                'fmt' => 'json',
            ],
        ]);

        $data = $response->toArray();

        $artist = new Artist();
        $artist->setName($data['name'] ?? 'Nom inconnu');
        $artist->setMbid($mbid);
        $artist->setBeginArea($data['begin-area']['name'] ?? null);

        // Tu peux ajouter d'autres champs ici (country, genre, alias, etc.)

        return $artist;
    }
}
