<?php

declare(strict_types=1);

namespace App\Controller;

use App\Type\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

final class SecurityController
{
    public function __construct(private Environment $environment)
    {
    }

    #[Route(path: '/connexion' , name: 'security_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return new Response($this->environment->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'menu' => 'login'
        ]));
    }

    #[Route(path: '/inscription' , name: 'security_registration', methods: ['GET', 'POST'])]
    public function registration(
        FormFactoryInterface $formFactory,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $form = $formFactory->create(RegistrationType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));

            $entityManager->persist($user);
            $entityManager->flush();

            return new RedirectResponse('/');
        }
        return new Response($this->environment->render('security/registration.html.twig', [
            'registration_form' => $form->createView(),
            'menu' => 'login'
        ]));
    }

    #[Route(path: '/deconnexion' , name: 'security_logout', methods: 'GET')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}