<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class PostSubscriber implements EventSubscriberInterface
{
    public function __construct(private TagAwareAdapterInterface $cache)
    {
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    public function postPersist(): void
    {
        $this->cache->invalidateTags(['blog_post']);
    }

    public function postRemove(): void
    {
        $this->cache->invalidateTags(['blog_post']);
    }

    public function postUpdate(): void
    {
        $this->cache->invalidateTags(['blog_post']);
    }
}