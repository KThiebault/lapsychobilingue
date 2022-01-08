<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\PostRepository;
use App\Service\FileUploader;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
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
        private TagAwareAdapterInterface $cache
    ) {
    }

    #[Route(path: '/blog' , name: 'admin_blog_index', methods: 'GET')]
    public function index(): Response
    {
        $posts = $this->cache->get('blog_post', function (ItemInterface $item) {
            $item->tag('blog_post');
            return $this->repository->findAll();
        });

        return new Response($this->twig->render('user/admin/blog/index.html.twig',
            [
                'posts' => $posts,
                'menu' => 'blog'
            ]
        ));
    }

    #[Route(path: '/blog/create', name: 'admin_blog_create', methods: ['GET', 'POST'])]
    public function create(Request $request, FileUploader $uploader, string $postUploadPath): Response
    {
        $form = $this->formBuilder->create(PostType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData()->setPicture(
                $uploader->upload(
                    $form->getData()->getUploadedFile(),
                    $postUploadPath
                )
            );

            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le post a bien été créé.');
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_blog_update', ['id' => $form->getData()->getId()])
            );
        }

        return new Response($this->twig->render('user/admin/blog/create.html.twig',
            [
                'create_form' => $form->createView(),
                'menu' => 'blog'
            ]
        ));
    }

    #[Route(path: '/blog/update/{id}', name: 'admin_blog_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(int $id, Request $request): Response {
        $post = $this->repository->find($id);
        if ($post === null) {
            return new RedirectResponse($this->urlGenerator->generate('admin_blog_index'));
        }

        $form = $this->formBuilder->create(PostType::class, $post)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le post a bien été mis à jour.');
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_blog_update', ['id' => $post->getId()])
            );
        }

        return new Response($this->twig->render('user/admin/blog/update.html.twig',
            [
                'update_form' => $form->createView(),
                'menu' => 'blog'
            ]
        ));
    }

    #[Route(path: '/blog/delete/{id}', name: 'admin_blog_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id, Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response {
        $post = $this->repository->find($id);

        if ($post === null) {
            return new RedirectResponse($this->urlGenerator->generate('admin_blog_index'));
        }
        if (
            $csrfTokenManager->isTokenValid(
                new CsrfToken('blog_delete' . $post->getId(), $request->get('_token')))
        ) {
            $this->entityManager->remove($post);
            $this->entityManager->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le post a bien été supprimé.');
        }

        return new RedirectResponse($this->urlGenerator->generate('admin_blog_index'));
    }
}