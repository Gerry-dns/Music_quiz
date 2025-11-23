<?php

namespace App\Entity;

use App\Repository\QuestionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionsRepository::class)]
class Questions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column(length: 255)]
    private ?string $correctAnswer = null;

    #[ORM\Column(length: 255)]
    private ?string $wrongAnswer1 = null;

    #[ORM\Column(length: 255)]
    private ?string $wrongAnswer2 = null;

    #[ORM\Column(length: 255)]
    private ?string $wrongAnswer3 = null;

    #[ORM\Column]
    private ?int $difficulty = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(nullable: true)]
    private ?int $yearHint = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $explanation = null;

    #[ORM\Column]
    private ?int $playedCount = null;

    #[ORM\Column]
    private ?int $correctCount = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artist $artist = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getCorrectAnswer(): ?string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(string $correctAnswer): static
    {
        $this->correctAnswer = $correctAnswer;

        return $this;
    }

    public function getWrongAnswer1(): ?string
    {
        return $this->wrongAnswer1;
    }

    public function setWrongAnswer1(string $wrongAnswer1): static
    {
        $this->wrongAnswer1 = $wrongAnswer1;

        return $this;
    }

    public function getWrongAnswer2(): ?string
    {
        return $this->wrongAnswer2;
    }

    public function setWrongAnswer2(string $wrongAnswer2): static
    {
        $this->wrongAnswer2 = $wrongAnswer2;

        return $this;
    }

    public function getWrongAnswer3(): ?string
    {
        return $this->wrongAnswer3;
    }

    public function setWrongAnswer3(string $wrongAnswer3): static
    {
        $this->wrongAnswer3 = $wrongAnswer3;

        return $this;
    }

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(int $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getYearHint(): ?int
    {
        return $this->yearHint;
    }

    public function setYearHint(?int $yearHint): static
    {
        $this->yearHint = $yearHint;

        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): static
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getPlayedCount(): ?int
    {
        return $this->playedCount;
    }

    public function setPlayedCount(int $playedCount): static
    {
        $this->playedCount = $playedCount;

        return $this;
    }

    public function getCorrectCount(): ?int
    {
        return $this->correctCount;
    }

    public function setCorrectCount(int $correctCount): static
    {
        $this->correctCount = $correctCount;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): static
    {
        $this->artist = $artist;

        return $this;
    }
}
