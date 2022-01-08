<?php

namespace App\Repository;

use App\Doctrine\Type\Day;
use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    public function findByPsychologist(int $id): array
    {
        return self::factory($this->createQueryBuilder('s')
            ->where('s.psychologist = :id')
            ->setParameter('id', $id)
            ->orderBy('s.startedAt', 'ASC')
            ->getQuery()
            ->getResult());
    }

    /**
     * @param array<array-key, Schedule> $schedules
     * @return array<string, array<array-key, Schedule>>
     */
    private static function factory(array $schedules): array
    {
        return self::orderByDay(self::groupByDay($schedules));
    }

    /**
     * @param array<array-key, Schedule> $schedules
     * @return array<string, array<array-key, Schedule>>
     */
    private static function groupByDay(array $schedules): array
    {
        foreach ($schedules as $schedule) {
            foreach (Day::cases() as $day) {
                if ($schedule->getDay() === $day) {
                    $orderedSchedules[$day->value][] = $schedule;
                }
            }
        }

        return $orderedSchedules ?? [];
    }

    /**
     * @param array<array-key, Schedule> $schedules
     * @return array<string, array<array-key, Schedule>>
     */
    private static function orderByDay(array $schedules): array
    {
        foreach (Day::cases() as $day) {
            if (array_key_exists($day->value, $schedules)) {
                $orderedSchedules[$day->value] = $schedules[$day->value];
            }
        }

        return $orderedSchedules ?? [];
    }
}