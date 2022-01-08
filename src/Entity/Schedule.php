<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\DaysType;
use App\Doctrine\Type\Day;
use App\Entity\Users\Psychologist;
use App\Repository\ScheduleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: ScheduleRepository::class)]
final class Schedule
{
    #[
        Id,
        GeneratedValue,
        Column(type: Types::INTEGER)
    ]
    private int $id;

    #[Column(type: DaysType::NAME, length: 255)]
    private Day $day;

    #[
        ManyToOne(targetEntity: Psychologist::class, inversedBy: 'schedules'),
        JoinColumn(onDelete: 'CASCADE')
    ]
    private Psychologist $psychologist;

    #[Column(type: Types::TIME_IMMUTABLE)]
    private \DateTimeImmutable $startedAt;

    #[Column(type: Types::TIME_IMMUTABLE)]
    private \DateTimeImmutable $endedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Schedule
    {
        $this->id = $id;
        return $this;
    }

    public function getDay(): Day
    {
        return $this->day;
    }

    public function setDay(Day $day): Schedule
    {
        $this->day = $day;
        return $this;
    }

    public function getPsychologist(): Psychologist
    {
        return $this->psychologist;
    }

    public function setPsychologist(Psychologist $psychologist): Schedule
    {
        $this->psychologist = $psychologist;
        return $this;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): Schedule
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getEndedAt(): \DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeImmutable $endedAt): Schedule
    {
        $this->endedAt = $endedAt;
        return $this;
    }
}