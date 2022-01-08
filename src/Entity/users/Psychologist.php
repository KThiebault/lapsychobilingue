<?php

declare(strict_types=1);

namespace App\Entity\Users;

use App\Entity\Schedule;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity]
class Psychologist extends User
{
    #[OneToMany(mappedBy: 'psychologist', targetEntity: Schedule::class)]
    private Collection $schedules;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
    }

    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function setSchedules(Collection $schedules): Psychologist
    {
        $this->schedules = $schedules;
        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_PSYCHOLOGIST'];
    }
}