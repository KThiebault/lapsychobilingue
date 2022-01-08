<?php

declare(strict_types=1);

namespace App\Form;

use App\Doctrine\Type\Day;
use App\Entity\Schedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('day', EnumType::class, [
                'label' => 'Jour :',
                'class' => Day::class
            ])
            ->add('startedAt', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Début à :',
                'input' => 'datetime_immutable'
            ])
            ->add('endedAt', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Fin à :',
                'input' => 'datetime_immutable'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Schedule::class);
    }

    private function getDayChoices(): array
    {
        foreach (Day::cases() as $key => $day) {
            $days[$day->name] = $key;
        }

        return $days ?? [];
    }
}