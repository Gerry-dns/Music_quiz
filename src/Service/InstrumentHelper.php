<?php

namespace App\Service;

class InstrumentHelper
{
    public function toFrench(string $instr): string
    {
        return match(strtolower($instr)) {
            'lead vocals' => 'le chanteur',
            'guitar' => 'guitariste',
            'bass guitar' => 'bassiste',
            'drum set', 'drums' => 'batteur',
            'drums (drum set)' => 'batteur',
            'keyboard', 'keyboards' => 'claviériste',
            'piano' => 'pianiste',
            default => $instr
        };
    }
}
