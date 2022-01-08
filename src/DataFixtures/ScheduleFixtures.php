<?php

namespace App\DataFixtures;

use App\Doctrine\Type\Day;
use App\Entity\Schedule;
use App\Entity\Users\Patient;
use App\Entity\Users\Psychologist;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function load(ObjectManager $manager)
    {
        $users = $this->repository->findAll();

        foreach ($users as $user) {
            if (!$user instanceof Patient) {
                for ($i = 1; $i < 12; $i++) {
                    $startedAt = (new \DateTimeImmutable())->setTime(random_int(0, 23), random_int(0, 59));
                    $schedule = (new Schedule())
                        ->setPsychologist($user)
                        ->setDay(Day::cases()[array_rand(Day::cases())])
                        ->setStartedAt($startedAt)
                        ->setEndedAt($startedAt->modify('+' . random_int(1, 5) . ' Hours'));
                    $manager->persist($schedule);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }
}