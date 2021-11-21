<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Users\Administrator;
use App\Entity\Users\Patient;
use App\Entity\Users\Psychologist;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

final class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 2; $i++) {
            $user = (new Administrator())
                ->setEmail(sprintf('admin%d@admin.fr', $i))
                ->setName(sprintf('test%d', $i))
                ->setAge(new \DateTimeImmutable('1995-12-08'))
                ->setNationality($i > 1 ? 'french' : 'italiano');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'fixture'));

            $manager->persist($user);
        }

        for ($i = 1; $i <= 30; $i++) {
            $user = (new Psychologist())
                ->setEmail(sprintf('psychologist%d@psychologist.fr', $i))
                ->setName(sprintf('test%d', $i))
                ->setAge(new \DateTimeImmutable('1995-12-08'))
                ->setNationality($i > 15 ? 'french' : 'italiano');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'fixture'));

            $manager->persist($user);
        }

        for ($i = 1; $i <= 50; $i++) {
            $user = (new Patient())
                ->setEmail(sprintf('patient%d@patient.fr', $i))
                ->setName(sprintf('test%d', $i))
                ->setAge(new \DateTimeImmutable('1995-12-08'))
                ->setNationality($i > 25 ? 'french' : 'italiano');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'fixture'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
