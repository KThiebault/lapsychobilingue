<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\PostRepository;
use App\Type\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

#[
    Route(path: '/admin'),
]
final class BlogController
{
    public function __construct(private Environment $twig, private PostRepository $repository)
    {
    }

    #[Route(path: '/blog' , name: 'admin_post_index', methods: 'GET')]
    public function index(): Response
    {
        return new Response($this->twig->render('admin/blog/index.html.twig',
            [
                'posts' => $this->repository->findAll(),
                'menu' => 'blog'
            ]
        ));
    }

    #[Route(path: '/blog/{id}' , name: 'admin_post_update', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        Request $request,
        UrlGeneratorInterface $urlGenerator,
        FormFactoryInterface $formBuilder,
        EntityManagerInterface $entityManager
    ): Response {
        $post = $this->repository->find($id);
        if (!$post) {
            return new RedirectResponse($urlGenerator->generate('home_index'));
        }

        $form = $formBuilder->create(PostType::class, $post)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($form->getData());
            $entityManager->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le post a bien été mis à jour.');
            return new RedirectResponse($urlGenerator->generate('admin_post_update', ['id' => $post->getId()]));
        }

        return new Response($this->twig->render('admin/blog/update.html.twig',
            [
                'update_form' => $form->createView(),
                'menu' => 'blog_update'
            ]
        ));
    }
}