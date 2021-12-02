<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\PostRepository;
use App\Service\FileUploader;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

#[Route(path: '/admin')]
final class BlogController
{
    public function __construct(
        private Environment $twig,
        private PostRepository $repository,
        private FormFactoryInterface $formBuilder,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager,
        private ContainerInterface $container
    ) {
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

    #[Route(path: '/blog/create', name: 'admin_post_create', methods: ['GET', 'POST'])]
    public function create(Request $request, FileUploader $uploader): Response
    {
        $form = $this->formBuilder->create(PostType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData()->setPicture(
                $uploader->upload(
                    $form->getData()->getUploadedFile(),
                    $this->container->getParameter('app.post_picture_directory')
                )
            );

            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le post a bien été créé.');
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_post_update', ['id' => $form->getData()->getId()])
            );
        }

        return new Response($this->twig->render('admin/blog/create.html.twig',
            [
                'create_form' => $form->createView(),
                'menu' => 'blog_create'
            ]
        ));
    }

    #[Route(path: '/blog/update/{id}', name: 'admin_post_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(int $id, Request $request): Response {
        $post = $this->repository->find($id);
        if ($post === null) {
            return new RedirectResponse($this->urlGenerator->generate('home_index'));
        }

        $form = $this->formBuilder->create(PostType::class, $post)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le post a bien été mis à jour.');
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_post_update', ['id' => $post->getId()])
            );
        }

        return new Response($this->twig->render('admin/blog/update.html.twig',
            [
                'update_form' => $form->createView(),
                'menu' => 'blog_update'
            ]
        ));
    }
}