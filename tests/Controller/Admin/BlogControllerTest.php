<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class BlogControllerTest extends WebTestCase
{
    /**
     * @dataProvider generateUri
     */
    public function testGoodUrl(string $method, string $uri): void
    {
        $client = self::createClient();
        $userRepository = $client->getContainer()->get(UserRepository::class);
        $client->loginUser($userRepository->findOneBy(['email' => 'admin1@admin.fr']));

        $client->request($method, $uri);

        self::assertResponseIsSuccessful();
    }

    /**
     * @dataProvider generateUri
     */
    public function testAccessDenied(string $method, string $uri): void
    {
        $client = self::createClient();
        $userRepository = $client->getContainer()->get(UserRepository::class);
        $client->loginUser($userRepository->findOneBy(['email' => 'psychologist1@psychologist.fr']));

        $client->request($method, $uri);
        $client->followRedirect();
        self::assertRouteSame('security_login');

        $client->loginUser($userRepository->findOneBy(['email' => 'patient1@patient.fr']));
        $client->request($method, $uri);
        $client->followRedirect();
        self::assertRouteSame('security_login');
    }

    public function testListingPost(): void
    {
        $client = self::createClient();

        $postRepository = $client->getContainer()->get(PostRepository::class);
        $userRepository = $client->getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneBy(['email' => 'admin1@admin.fr']));

        $crawler = $client->request('GET', '/admin/blog');

        self::assertCount($postRepository->count([]), $crawler->filter('tbody tr'));
    }

    public function testUpdatePostWithGoodData(): void
    {
        $data = [
            'post[title]' => 'test title',
            'post[summary]' => 'summary test',
            'post[content]' => 'content test',
            'post[online]' => false,
        ];
        $client = self::createClient();

        $postRepository = $client->getContainer()->get(PostRepository::class);
        $userRepository = $client->getContainer()->get(UserRepository::class);
        $client->loginUser($userRepository->findOneBy(['email' => 'admin1@admin.fr']));

        $post = $postRepository->findOneBy([]);

        $client->request('GET', '/admin/blog/' . $post->getId());
        $client->submitForm('Modifier', $data);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $updatedPost = $postRepository->findOneBy(['id' => $post->getId()]);
        $client->followRedirect();

        self::assertEquals($data['post[title]'], $updatedPost->getTitle());
        self::assertEquals($data['post[summary]'], $updatedPost->getSummary());
        self::assertEquals($data['post[content]'], $updatedPost->getContent());
        self::assertEquals($data['post[online]'], $updatedPost->isOnline());
        self::assertSelectorTextSame('.flash-success', 'Le post a bien été mis à jour.');
    }

    /**
     * @dataProvider generateBadData
     */
    public function testUpdatePostWithBadData(array $data, string $errorMessage): void
    {
        $client = self::createClient();

        $postRepository = $client->getContainer()->get(PostRepository::class);
        $post = $postRepository->findOneBy([]);

        $userRepository = $client->getContainer()->get(UserRepository::class);
        $client->loginUser($userRepository->findOneBy(['email' => 'admin1@admin.fr']));

        $client->request('GET', '/admin/blog/' . $post->getId());
        $client->submitForm('Modifier', $data);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $updatedPost = $postRepository->findOneBy(['id' => $post->getId()]);

        self::assertEquals($post->getTitle(), $updatedPost->getTitle());
        self::assertEquals($post->getContent(), $updatedPost->getContent());
        self::assertEquals($post->getSummary(), $updatedPost->getSummary());
        self::assertEquals($post->isOnline(), $updatedPost->isOnline());
        self::assertSelectorTextSame('ul.form__errors > li', $errorMessage);
    }

    public function generateUri(): \Generator
    {
        yield [
            'GET',
            '/admin/blog'
        ];
        yield [
            'GET',
            '/admin/blog/25'
        ];
    }

    public function generateBadData(): \Generator
    {
        yield [
            [
                'post[title]' => '',
                'post[summary]' => 'summary test',
                'post[content]' => 'content test',
                'post[online]' => false,
            ],
            'This value should not be blank.'
        ];
        yield [
            [
                'post[title]' => '12345',
                'post[summary]' => 'summary test',
                'post[content]' => 'content test',
                'post[online]' => false,
            ],
            'This value is too short. It should have 6 characters or more.'
        ];
        yield [
            [
                'post[title]' => 'title test',
                'post[summary]' => '',
                'post[content]' => 'content test',
                'post[online]' => false,
            ],
            'This value should not be blank.'
        ];
        yield [
            [
                'post[title]' => 'title test',
                'post[summary]' => '12345678',
                'post[content]' => 'content test',
                'post[online]' => false,
            ],
            'This value is too short. It should have 10 characters or more.'
        ];
        yield [
            [
                'post[title]' => 'title test',
                'post[summary]' => 'summary test',
                'post[content]' => '',
                'post[online]' => false,
            ],
            'This value should not be blank.'
        ];
        yield [
            [
                'post[title]' => 'title test',
                'post[summary]' => 'summary test',
                'post[content]' => '12345678',
                'post[online]' => false,
            ],
            'This value is too short. It should have 10 characters or more.'
        ];
    }
}