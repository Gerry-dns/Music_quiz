<?php

namespace App\Service;

class InstrumentNormalizerService
{
    private array $instrumentMap = [
        'guitare' => ['guitare', 'guitare basse', 'guitare électrique', 'guitare acoustique'],
        'batterie' => ['batterie', 'percussions'],
        'voix' => ['voix', 'chant', 'harmonica'],
        'clavier' => ['clavier', 'piano', 'orgue', 'instrument à clavier'],
    ];

    public function getMainInstrument(string $instrument): ?string
    {
        $lower = strtolower($instrument);

        foreach ($this->instrumentMap as $main => $variants) {
            foreach ($variants as $variant) {
                if (str_contains($lower, strtolower($variant))) {
                    return $main;
                }
            }
        }

        return null;
    }
}
