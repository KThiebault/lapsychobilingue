<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return array<int, Post>
     */
    public function findLatest(int $maxResults = 3):array
    {
        return $this->createQueryBuilder('p')
            ->where('p.online = 1')
            ->andWhere('p.onlineAt <= :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d h:i:s'))
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }
}