<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PostRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use phpDocumentor\Reflection\Types\True_;
use Symfony\Component\String\Slugger\SluggerInterface;

#[
    Entity(repositoryClass: PostRepository::class),
    HasLifecycleCallbacks
]
class Post
{
    #[
        Id,
        GeneratedValue,
        Column(type: 'integer')
    ]
    private int $id;

    #[Column(type: 'string', unique: true)]
    private string $slug;

    #[Column(type: 'string', unique: true)]
    private string $title;

    #[Column(type: 'text')]
    private string $content;

    #[Column(type: 'text')]
    private string $summary;

    #[Column(type: 'string')]
    private string $picture;

    #[Column(type: 'boolean', options: ['default' => true])]
    private bool $online = true;

    #[Column(type: 'datetime')]
    private DateTime $onlineAt;

    #[Column(type: 'datetime', nullable: true)]
    private DateTime $updatedAt;

    #[Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(private SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    #[
        PrePersist,
        PreUpdate
    ]
    public function setSlug(): Post
    {
        $this->slug = $this->slugger->slug($this->getTitle())->lower()->toString();
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Post
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): Post
    {
        $this->content = $content;
        return $this;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): Post
    {
        $this->summary = $summary;
        return $this;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): Post
    {
        $this->picture = $picture;
        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): Post
    {
        $this->online = $online;
        return $this;
    }

    public function getOnlineAt(): DateTime
    {
        return $this->onlineAt;
    }

    public function setOnlineAt(DateTime $onlineAt): Post
    {
        $this->onlineAt = $onlineAt;
        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    #[PreUpdate]
    public function setUpdatedAt(): Post
    {
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[PrePersist]
    public function setCreatedAt(): Post
    {
        $this->createdAt = new DateTimeImmutable();
        return $this;
    }
}