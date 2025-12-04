<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Form\MemberType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ArtistCrudController extends AbstractCrudController
{
    public function __construct(
        private HttpClientInterface $client,
        private \App\Service\WikipediaByNameService $wiki
    ) {}

    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom'),
            TextField::new('mbid', 'MBID')->hideOnIndex(),

            AssociationField::new('mainGenre', 'Genre principal'),
            AssociationField::new('country', 'Pays')
                ->setFormTypeOption('choice_label', 'name'),

            TextField::new('beginArea', 'Ville de formation')->hideOnIndex(),
            IntegerField::new('foundedYear', 'Année de création'),
            ImageField::new('coverImage', 'Image')
                ->setBasePath('')
                ->onlyOnDetail(),
            TextareaField::new('biography', 'Biographie')->hideOnIndex(),
            ArrayField::new('albums', 'Albums')->onlyOnDetail()->setTemplatePath('admin/fields/array_list.html.twig'),
            ArrayField::new('subGenres', 'Sous-genres')->onlyOnDetail()->setTemplatePath('admin/fields/array_list.html.twig'),
            ArrayField::new('members', 'Membres')->onlyOnDetail()->setTemplatePath('admin/fields/array_list.html.twig'),
            CollectionField::new('members', 'Membres')
                ->setEntryType(MemberType::class)
                ->allowAdd()
                ->allowDelete()
                ->onlyOnForms(),
            TextField::new('spotifyUrl')->onlyOnDetail(),
            TextField::new('youtubeUrl')->onlyOnDetail(),
            TextField::new('wikidataUrl')->onlyOnDetail(),
        ];
    }

    private function curlRequest(string $url, array $query): ?array
    {
        $fullUrl = $url . '?' . http_build_query($query);
        $cmd = "curl -s -A 'MusicQuiz/1.0' " . escapeshellarg($fullUrl);
        $result = shell_exec($cmd);
        return $result ? json_decode($result, true) : null;
    }

    private function safeRequest(string $url, array $query, int $retry = 3): ?array
    {
        while ($retry > 0) {
            try {
                $response = $this->client->request('GET', $url, [
                    'headers' => ['User-Agent' => 'MusicQuiz/1.0'],
                    'query' => $query,
                    'http_version' => '1.1',
                    'timeout' => 20,
                ]);
                return $response->toArray();
            } catch (\Throwable $e) {
                $retry--;
                sleep(2);
                if ($retry === 0) {
                    return $this->curlRequest($url, $query);
                }
            }
        }
        return null;
    }

    public function fetchFromMusicBrainz(EntityManagerInterface $em): RedirectResponse
    {
        $artist = $this->getContext()->getEntity()->getInstance();
        $mbid = trim($artist->getMbid());

        if (!$mbid || !preg_match('/^[0-9a-fA-F-]{36}$/', $mbid)) {
            $this->addFlash('danger', 'MBID invalide');
            return $this->redirect($this->generateUrl('admin', [
                'crudAction' => 'edit',
                'crudControllerFqcn' => self::class,
                'entityId' => $artist->getId(),
            ]));
        }

        try {
            $mainData = $this->safeRequest("https://musicbrainz.org/ws/2/artist/{$mbid}", [
                'inc' => 'releases+aliases+annotation+genres+tags+artist-rels+url-rels',
                'fmt' => 'json'
            ]);



            if (!$mainData) {
                $this->addFlash('danger', "Impossible de récupérer l'artiste depuis MusicBrainz");
                return $this->redirect($this->generateUrl('admin', [
                    'crudAction' => 'edit',
                    'crudControllerFqcn' => self::class,
                    'entityId' => $artist->getId(),
                ]));
            }
            //  dd($mainData);
            // --- Nom et bio ---
            $artist->setName($mainData['name'] ?? $artist->getName());
            $wikiResult = $this->wiki->fetchSummaryByName($artist->getName());
            if ($wikiResult) {
                $artist->setBiography($wikiResult['summary'] ?? null);
                if (!empty($wikiResult['image'])) {
                    $artist->setCoverImage($wikiResult['image']);
                }
            }
            $countryNames = [
                'AF' => 'Afghanistan',
                'AL' => 'Albania',
                'DZ' => 'Algeria',
                'AS' => 'American Samoa',
                'AD' => 'Andorra',
                'AO' => 'Angola',
                'AI' => 'Anguilla',
                'AQ' => 'Antarctica',
                'AG' => 'Antigua and Barbuda',
                'AR' => 'Argentina',
                'AM' => 'Armenia',
                'AW' => 'Aruba',
                'AU' => 'Australia',
                'AT' => 'Austria',
                'AZ' => 'Azerbaijan',
                'BS' => 'Bahamas',
                'BH' => 'Bahrain',
                'BD' => 'Bangladesh',
                'BB' => 'Barbados',
                'BY' => 'Belarus',
                'BE' => 'Belgium',
                'BZ' => 'Belize',
                'BJ' => 'Benin',
                'BM' => 'Bermuda',
                'BT' => 'Bhutan',
                'BO' => 'Bolivia',
                'BQ' => 'Bonaire, Sint Eustatius and Saba',
                'BA' => 'Bosnia and Herzegovina',
                'BW' => 'Botswana',
                'BV' => 'Bouvet Island',
                'BR' => 'Brazil',
                'IO' => 'British Indian Ocean Territory',
                'BN' => 'Brunei Darussalam',
                'BG' => 'Bulgaria',
                'BF' => 'Burkina Faso',
                'BI' => 'Burundi',
                'CV' => 'Cabo Verde',
                'KH' => 'Cambodia',
                'CM' => 'Cameroon',
                'CA' => 'Canada',
                'KY' => 'Cayman Islands',
                'CF' => 'Central African Republic',
                'TD' => 'Chad',
                'CL' => 'Chile',
                'CN' => 'China',
                'CX' => 'Christmas Island',
                'CC' => 'Cocos (Keeling) Islands',
                'CO' => 'Colombia',
                'KM' => 'Comoros',
                'CG' => 'Congo',
                'CD' => 'Congo (Democratic Republic)',
                'CK' => 'Cook Islands',
                'CR' => 'Costa Rica',
                'CI' => "Côte d'Ivoire",
                'HR' => 'Croatia',
                'CU' => 'Cuba',
                'CW' => 'Curaçao',
                'CY' => 'Cyprus',
                'CZ' => 'Czechia',
                'DK' => 'Denmark',
                'DJ' => 'Djibouti',
                'DM' => 'Dominica',
                'DO' => 'Dominican Republic',
                'EC' => 'Ecuador',
                'EG' => 'Egypt',
                'SV' => 'El Salvador',
                'GQ' => 'Equatorial Guinea',
                'ER' => 'Eritrea',
                'EE' => 'Estonia',
                'SZ' => 'Eswatini',
                'ET' => 'Ethiopia',
                'FK' => 'Falkland Islands',
                'FO' => 'Faroe Islands',
                'FJ' => 'Fiji',
                'FI' => 'Finland',
                'FR' => 'France',
                'GF' => 'French Guiana',
                'PF' => 'French Polynesia',
                'TF' => 'French Southern Territories',
                'GA' => 'Gabon',
                'GM' => 'Gambia',
                'GE' => 'Georgia',
                'DE' => 'Germany',
                'GH' => 'Ghana',
                'GI' => 'Gibraltar',
                'GR' => 'Greece',
                'GL' => 'Greenland',
                'GD' => 'Grenada',
                'GP' => 'Guadeloupe',
                'GU' => 'Guam',
                'GT' => 'Guatemala',
                'GG' => 'Guernsey',
                'GN' => 'Guinea',
                'GW' => 'Guinea-Bissau',
                'GY' => 'Guyana',
                'HT' => 'Haiti',
                'HM' => 'Heard Island and McDonald Islands',
                'VA' => 'Holy See',
                'HN' => 'Honduras',
                'HK' => 'Hong Kong',
                'HU' => 'Hungary',
                'IS' => 'Iceland',
                'IN' => 'India',
                'ID' => 'Indonesia',
                'IR' => 'Iran',
                'IQ' => 'Iraq',
                'IE' => 'Ireland',
                'IM' => 'Isle of Man',
                'IL' => 'Israel',
                'IT' => 'Italy',
                'JM' => 'Jamaica',
                'JP' => 'Japan',
                'JE' => 'Jersey',
                'JO' => 'Jordan',
                'KZ' => 'Kazakhstan',
                'KE' => 'Kenya',
                'KI' => 'Kiribati',
                'KP' => "Korea (North)",
                'KR' => "Korea (South)",
                'KW' => 'Kuwait',
                'KG' => 'Kyrgyzstan',
                'LA' => "Lao People's Democratic Republic",
                'LV' => 'Latvia',
                'LB' => 'Lebanon',
                'LS' => 'Lesotho',
                'LR' => 'Liberia',
                'LY' => 'Libya',
                'LI' => 'Liechtenstein',
                'LT' => 'Lithuania',
                'LU' => 'Luxembourg',
                'MO' => 'Macao',
                'MG' => 'Madagascar',
                'MW' => 'Malawi',
                'MY' => 'Malaysia',
                'MV' => 'Maldives',
                'ML' => 'Mali',
                'MT' => 'Malta',
                'MH' => 'Marshall Islands',
                'MQ' => 'Martinique',
                'MR' => 'Mauritania',
                'MU' => 'Mauritius',
                'YT' => 'Mayotte',
                'MX' => 'Mexico',
                'FM' => 'Micronesia',
                'MD' => 'Moldova',
                'MC' => 'Monaco',
                'MN' => 'Mongolia',
                'ME' => 'Montenegro',
                'MS' => 'Montserrat',
                'MA' => 'Morocco',
                'MZ' => 'Mozambique',
                'MM' => 'Myanmar',
                'NA' => 'Namibia',
                'NR' => 'Nauru',
                'NP' => 'Nepal',
                'NL' => 'Netherlands',
                'NC' => 'New Caledonia',
                'NZ' => 'New Zealand',
                'NI' => 'Nicaragua',
                'NE' => 'Niger',
                'NG' => 'Nigeria',
                'NU' => 'Niue',
                'NF' => 'Norfolk Island',
                'MK' => 'North Macedonia',
                'MP' => 'Northern Mariana Islands',
                'NO' => 'Norway',
                'OM' => 'Oman',
                'PK' => 'Pakistan',
                'PW' => 'Palau',
                'PS' => 'Palestine',
                'PA' => 'Panama',
                'PG' => 'Papua New Guinea',
                'PY' => 'Paraguay',
                'PE' => 'Peru',
                'PH' => 'Philippines',
                'PN' => 'Pitcairn',
                'PL' => 'Poland',
                'PT' => 'Portugal',
                'PR' => 'Puerto Rico',
                'QA' => 'Qatar',
                'RE' => 'Réunion',
                'RO' => 'Romania',
                'RU' => 'Russia',
                'RW' => 'Rwanda',
                'BL' => 'Saint Barthélemy',
                'SH' => 'Saint Helena',
                'KN' => 'Saint Kitts and Nevis',
                'LC' => 'Saint Lucia',
                'MF' => 'Saint Martin (French part)',
                'PM' => 'Saint Pierre and Miquelon',
                'VC' => 'Saint Vincent and the Grenadines',
                'WS' => 'Samoa',
                'SM' => 'San Marino',
                'ST' => 'Sao Tome and Principe',
                'SA' => 'Saudi Arabia',
                'SN' => 'Senegal',
                'RS' => 'Serbia',
                'SC' => 'Seychelles',
                'SL' => 'Sierra Leone',
                'SG' => 'Singapore',
                'SX' => 'Sint Maarten (Dutch part)',
                'SK' => 'Slovakia',
                'SI' => 'Slovenia',
                'SB' => 'Solomon Islands',
                'SO' => 'Somalia',
                'ZA' => 'South Africa',
                'GS' => 'South Georgia and the South Sandwich Islands',
                'SS' => 'South Sudan',
                'ES' => 'Spain',
                'LK' => 'Sri Lanka',
                'SD' => 'Sudan',
                'SR' => 'Suriname',
                'SE' => 'Sweden',
                'CH' => 'Switzerland',
                'SY' => 'Syria',
                'TW' => 'Taiwan',
                'TJ' => 'Tajikistan',
                'TZ' => 'Tanzania',
                'TH' => 'Thailand',
                'TL' => 'Timor-Leste',
                'TG' => 'Togo',
                'TK' => 'Tokelau',
                'TO' => 'Tonga',
                'TT' => 'Trinidad and Tobago',
                'TN' => 'Tunisia',
                'TR' => 'Turkey',
                'TM' => 'Turkmenistan',
                'TC' => 'Turks and Caicos Islands',
                'TV' => 'Tuvalu',
                'UG' => 'Uganda',
                'UA' => 'Ukraine',
                'AE' => 'United Arab Emirates',
                'GB' => 'United Kingdom',
                'US' => 'United States',
                'UM' => 'United States Minor Outlying Islands',
                'UY' => 'Uruguay',
                'UZ' => 'Uzbekistan',
                'VU' => 'Vanuatu',
                'VE' => 'Venezuela',
                'VN' => 'Vietnam',
                'VG' => 'Virgin Islands (British)',
                'VI' => 'Virgin Islands (U.S.)',
                'WF' => 'Wallis and Futuna',
                'EH' => 'Western Sahara',
                'YE' => 'Yemen',
                'ZM' => 'Zambia',
                'ZW' => 'Zimbabwe',
            ];


            // --- Pays ---
            $countryCode = $mainData['country'] ?? null;

            // Conversion en nom complet
            $countryName = $countryNames[$countryCode] ?? $countryCode; // Si code inconnu, on met le code brut

            if ($countryName) {
                $countryRepo = $em->getRepository(\App\Entity\Country::class);
                $country = $countryRepo->findOneBy(['name' => $countryName]);

                if (!$country) {
                    $country = new \App\Entity\Country();
                    $country->setName($countryName);
                    $country->setIsoCode($countryCode); // si tu as un champ isoCode
                    $em->persist($country);
                }

                $artist->setCountry($country);
            }

            $beginAreaName = $mainData['begin-area']['name'] ?? null;
            $artist->setBeginArea($beginAreaName);


            // --- Ville de formation ---
            $artist->setBeginArea($mainData['begin-area']['name'] ?? null);

            // --- Année de création ---
            if (!empty($mainData['life-span']['begin'])) {
                $artist->setFoundedYear((int) substr($mainData['life-span']['begin'], 0, 4));
            }

            // --- Albums ---
            $albums = array_map(fn($r) => $r['title'] ?? '', $mainData['releases'] ?? []);
            $albums = array_unique(array_filter($albums));
            $artist->setAlbums($albums);

            // --- Membres ---
            $members = [];
            foreach ($mainData['relations'] ?? [] as $rel) {
                if (($rel['type'] ?? '') === 'member of band' && isset($rel['artist']['name'])) {
                    $name = trim($rel['artist']['name']);
                    $exists = false;
                    foreach ($members as $m) {
                        if (strcasecmp($m['name'], $name) === 0) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $members[] = [
                            'name' => $name,
                            'instruments' => $rel['attribute-list'] ?? [],
                        ];
                    }
                }
            }
            $artist->setMembers($members);

            // --- URLs ---
            $relationMap = [
                'youtube' => 'youtubeUrl',
                'wikidata' => 'wikidataUrl',
                'spotify' => 'spotifyUrl',
                'deezer' => 'deezerUrl',
                'bandcamp' => 'bandcampUrl',
                'discogs' => 'discogsUrl',
                'official homepage' => 'officialSiteUrl',
                'soundcloud' => 'soundcloudUrl',
                'last.fm' => 'lastfmUrl',
                'twitter' => 'twitterUrl',
                'facebook' => 'facebookUrl',
                'instagram' => 'instagramUrl',
                'lyrics' => 'geniusUrl'
            ];
            foreach ($mainData['relations'] ?? [] as $rel) {
                $type = strtolower($rel['type'] ?? '');
                $resource = $rel['url']['resource'] ?? null;
                if ($resource && isset($relationMap[$type])) {
                    $setter = 'set' . ucfirst($relationMap[$type]);
                    if (method_exists($artist, $setter)) {
                        $artist->$setter($resource);
                    }
                }
            }

            // --- Genres ---
            $mainGenre = null;
            $subGenres = [];
            if (!empty($mainData['genres'])) {
                usort($mainData['genres'], fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
                $mainGenre = $mainData['genres'][0]['name'] ?? null;
                foreach ($mainData['genres'] as $g) {
                    if ($g['name'] !== $mainGenre) $subGenres[] = $g['name'];
                }
            }
            if (!empty($mainData['tags'])) {
                usort($mainData['tags'], fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
                if (!$mainGenre) $mainGenre = $mainData['tags'][0]['name'] ?? null;
                foreach ($mainData['tags'] as $t) {
                    if ($t['name'] !== $mainGenre && !in_array($t['name'], $subGenres)) {
                        $subGenres[] = $t['name'];
                    }
                }
            }
            if ($mainGenre) {
                $slug = strtolower(trim($mainGenre));
                $genreRepo = $em->getRepository(\App\Entity\Genre::class);
                $genre = $genreRepo->findOneBy(['slug' => $slug]);
                if (!$genre) {
                    $genre = new \App\Entity\Genre();
                    $genre->setName($mainGenre);
                    $genre->setSlug($slug);
                    $em->persist($genre);
                }
                $artist->setMainGenre($genre);
            }
            $artist->setSubGenres($subGenres);

            $artist->setDisambiguation($mainData['disambiguation'] ?? null);

            $em->flush();
            $this->addFlash('success', 'Données récupérées depuis MusicBrainz !');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($this->generateUrl('admin', [
            'crudAction' => 'edit',
            'crudControllerFqcn' => self::class,
            'entityId' => $artist->getId(),
        ]));
    }

    public function configureActions(Actions $actions): Actions
    {
        $fetch = Action::new('fetchFromMusicBrainz', 'Récupérer depuis MusicBrainz', 'fa fa-sync')
            ->linkToCrudAction('fetchFromMusicBrainz')
            ->addCssClass('btn btn-success');

        return $actions
            ->add(Crud::PAGE_EDIT, $fetch)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
