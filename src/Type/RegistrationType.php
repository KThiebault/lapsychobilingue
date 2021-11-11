<?php

namespace App\Type;

use App\Entity\User;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail:'
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom:'
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'required' => true,
                'type' => PasswordType::class,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 6,
                        'max' => 255,
                    ]),
                ],
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'Mot de passe:'],
                'second_options' => ['label' => 'Confirmer votre mot de passe:']
            ])
            ->add('age', BirthdayType::class, [
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'label' => 'Age:',
                'attr' => [
                    'min' => (new DateTime(date('Y-m-d')))->modify('- 80 years')->format('Y-m-d'),
                    'max' => (new DateTime(date('Y-m-d')))->modify('- 18 years')->format('Y-m-d')
                ]
            ])
            ->add('nationality', ChoiceType::class, [
                'label' => 'Nationalité:',
                'choices' => [
                    'Française' => 'french',
                    'Italiano' => 'italian'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', User::class);
    }

}