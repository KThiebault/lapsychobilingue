<?php

declare(strict_types=1);

namespace App\Entity\Users;

use App\Entity\User;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
final class Psychologist extends User
{
    public function getRoles(): array
    {
        return ['ROLE_PSYCHOLOGIST'];
    }
}