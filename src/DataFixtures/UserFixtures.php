<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 100; $i++) {
            $user = (new User())
                ->setEmail(sprintf('fixture%d@fixture.fr', $i))
                ->setName(sprintf('test%d', $i))
                ->setAge(new \DateTimeImmutable('1995-12-08'))
                ->setNationality($i > 50 ? 'french' : 'italiano');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'fixture'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
