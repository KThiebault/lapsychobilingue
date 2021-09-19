<?php

declare(strict_types=1);

namespace App\Controller;

use App\Type\RegistrationType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class SecurityController
{
    #[Route(path: '/connexion' , name: 'security_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils$authenticationUtils, Environment $environment): Response
    {
        return new Response($environment->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'menu' => 'login'
        ]));
    }

    #[Route(path: '/inscription' , name: 'security_registration', methods: ['GET', 'POST'])]
    public function registration(FormFactoryInterface $formFactory, Environment $environment): Response
    {
        $form = $formFactory->create(RegistrationType::class);
        return new Response($environment->render('security/registration.html.twig', [
            'registration_form' => $form->createView(),
            'menu' => 'login'
        ]));
    }
}