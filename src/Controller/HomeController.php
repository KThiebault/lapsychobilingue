<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class HomeController
{
    #[Route(path: '/' , name: 'home_index', methods: 'GET')]
    public function index(Environment $twig, PostRepository $repository):Response
    {
        return new Response($twig->render('home/index.html.twig',
            [
                'posts' => $repository->findLatest()
            ]
        ));
    }
}