<?php

declare(strict_types=1);

namespace App\Type;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

final class PostType extends AbstractType
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre :',
                'empty_data' => ''
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé :',
                'empty_data' => ''
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu :',
                'empty_data' => ''
            ])
            ->add('onlineAt', DateTimeType::class, [
                'label' => 'Date & heure de publication :'
            ])
            ->add('online', CheckboxType::class, [
                'label' => 'En ligne ?',
                'required' => false
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $post = $event->getData();
                if (null !== $post->getTitle()) {
                    $post->setSlug($this->slugger->slug($post->getTitle())->lower()->toString());
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Post::class);
    }

}