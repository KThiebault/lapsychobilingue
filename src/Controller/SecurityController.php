<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class SecurityController
{
    #[Route(path: '/login' , name: 'security_login', methods: ['GET', 'POST'])]
    public function login(Environment $environment): Response
    {
        return new Response($environment->render('security/login.html.twig', [
            'menu' => 'login'
        ]));
    }
}