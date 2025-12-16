<?php

namespace App\Service;

class ArtistMemberMergerService
{
    public function __construct(
        private InstrumentNormalizerService $instrumentNormalizer
    ) {}

    public function merge(array $existingMembers, array $wikidataMembers): array
    {
        $existingNames = array_column($existingMembers, 'name');
        $processed = [];

        foreach ($wikidataMembers as $name => $instruments) {
            $main = [];

            foreach ($instruments as $instr) {
                $type = $this->instrumentNormalizer->getMainInstrument($instr);
                if ($type) {
                    $main[$type] = true;
                }
            }

            $processed[$name] = [
                'details' => $instruments,
                'main' => array_keys($main),
            ];
        }

        foreach ($existingMembers as &$member) {
            if (isset($processed[$member['name']])) {
                $member['instruments'] = array_unique(array_merge(
                    $member['instruments'] ?? [],
                    $processed[$member['name']]['details']
                ));
                $member['mainInstruments'] = $processed[$member['name']]['main'];
            }
        }

        foreach ($processed as $name => $info) {
            if (!in_array($name, $existingNames, true)) {
                $existingMembers[] = [
                    'name' => $name,
                    'instruments' => $info['details'],
                    'mainInstruments' => $info['main'],
                ];
            }
        }

        return $existingMembers;
    }
}
