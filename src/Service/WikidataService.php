<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WikidataService
{
    public function __construct(
        private HttpClientInterface $client
    ) {}

    public function fetchMembers(string $wikidataId): array
    {
        $endpoint = 'https://query.wikidata.org/sparql';

        $query = <<<SPARQL
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>

SELECT ?memberLabel ?instrumentLabel WHERE {
    wd:$wikidataId wdt:P527 ?member .
    OPTIONAL { ?member wdt:P1303 ?instrument . }
    SERVICE wikibase:label { bd:serviceParam wikibase:language "fr,en". }
}
SPARQL;

        $response = $this->client->request('GET', $endpoint, [
            'headers' => ['Accept' => 'application/sparql-results+json'],
            'query' => ['query' => $query],
        ]);

        $data = $response->toArray();
        $result = [];

        foreach ($data['results']['bindings'] as $row) {
            $name = $row['memberLabel']['value'] ?? null;
            $instrument = $row['instrumentLabel']['value'] ?? null;

            if ($name) {
                $result[$name] ??= [];
                if ($instrument && !in_array($instrument, $result[$name], true)) {
                    $result[$name][] = $instrument;
                }
            }
        }

        return $result;
    }
}
