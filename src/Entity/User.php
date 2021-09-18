<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[
    Entity(repositoryClass: UserRepository::class),
]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[
        Id,
        GeneratedValue,
        Column(type: 'integer')
    ]
    private int $id;

    #[Column(type: 'string', unique: true)]
    private string $email;

    #[Column(type: 'string')]
    private string $password;

    #[Column(type: 'json')]
    private array $roles = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getSalt(){}
    public function eraseCredentials(){}
}