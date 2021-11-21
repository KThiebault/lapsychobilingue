<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Users\Administrator;
use App\Entity\Users\Patient;
use App\Entity\Users\Psychologist;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

#[
    Entity(repositoryClass: UserRepository::class),
    UniqueEntity('email'),
    InheritanceType('SINGLE_TABLE'),
    DiscriminatorColumn(name: 'discriminator', type: 'string'),
    DiscriminatorMap(
        [
            'patient' => Patient::class,
            'psychologist' => Psychologist::class,
            'administrator' => Administrator::class,
        ]
    )
]
abstract class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[
        Id,
        GeneratedValue,
        Column(type: 'integer')
    ]
    private int $id;

    #[
        Column(type: 'string', length: 255, unique: true),
        Assert\NotBlank,
        Assert\Email
    ]
    private string $email;

    #[Column(type: 'string')]
    private string $password;

    #[
        Column(type: 'string', length: 255),
        Assert\NotBlank,
        Assert\Length(
            min: 2,
            max: 25,
        )
    ]
    private string $name;

    #[
        Column(type: 'string', length: 255),
        Assert\NotBlank,
        Assert\Choice(['french', 'italian'])
    ]
    private string $nationality;

    #[
        Column(type: 'date_immutable'),
        Assert\NotBlank,
        Assert\LessThanOrEqual('-18 years', message: 'You must be 18 years or older.')
    ]
    private ?\DateTimeImmutable $age;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getNationality(): string
    {
        return $this->nationality;
    }

    public function setNationality(string $nationality): self
    {
        $this->nationality = $nationality;
        return $this;
    }

    public function getAge(): \DateTimeImmutable
    {
        return $this->age;
    }

    public function setAge(?\DateTimeImmutable $age): self
    {
        $this->age = $age;
        return $this;
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