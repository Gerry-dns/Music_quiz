<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\MemberInstrumentRepository::class)]
class MemberInstrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy:'memberInstruments')]
    #[ORM\JoinColumn(nullable:false)]
    private ?Member $member = null;

    #[ORM\ManyToOne(targetEntity: Instrument::class)]
    #[ORM\JoinColumn(nullable:false)]
    private ?Instrument $instrument = null;

    public function getId(): ?int { return $this->id; }
    public function getMember(): ?Member { return $this->member; }
    public function setMember(?Member $member): self { $this->member = $member; return $this; }
    public function getInstrument(): ?Instrument { return $this->instrument; }
    public function setInstrument(?Instrument $instrument): self { $this->instrument = $instrument; return $this; }
}
