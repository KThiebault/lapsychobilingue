<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    public function testCreatePostWithGoodData(): void
    {
        $client = self::createClient();
        $projectDir = $client->getContainer()->getParameter('kernel.project_dir');
        $data = [
            'post[title]' => 'test title',
            'post[summary]' => 'summary test',
            'post[content]' => 'content test',
            'post[onlineAt]' => (new \DateTime())->format('Y-m-d h:i:s'),
            'post[uploadedFile]' => new UploadedFile(
                $projectDir . '/src/DataFixtures/images/fixture.png',
                'fixture.png'
            ),
            'post[online]' => false,
        ];

        $postRepository = $client->getContainer()->get(PostRepository::class);
        $userRepository = $client->getContainer()->get(UserRepository::class);
        $client->loginUser($userRepository->findOneBy(['email' => 'admin1@admin.fr']));

        $client->request('GET', '/admin/blog/create');
        $client->submitForm('Créer', $data);
        $post = $postRepository->findOneBy(['slug' => 'test-title']);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();

        self::assertEquals($data['post[title]'], $post->getTitle());
        self::assertEquals($data['post[summary]'], $post->getSummary());
        self::assertEquals($data['post[content]'], $post->getContent());
        self::assertEquals($data['post[online]'], $post->isOnline());
        self::assertFileExists(
            $client->getContainer()->getParameter('app.post_picture_directory') . $post->getPicture()
        );
        self::assertSelectorTextSame('.flash-success', 'Le post a bien été créé.');
    }

    public function testUpdatePostWithGoodData(): void
    {
        $client = self::createClient();
        $projectDir = $client->getContainer()->getParameter('kernel.project_dir');

        $data = [
            'post[title]' => 'test title',
            'post[summary]' => 'summary test',
            'post[content]' => 'content test',
            'post[uploadedFile]' => new UploadedFile(
                $projectDir . '/src/DataFixtures/images/fixture.png',
                'fixture.png'
            ),
            'post[online]' => false,
        ];

        $postRepository = $client->getContainer()->get(PostRepository::class);
        $userRepository = $client->getContainer()->get(UserRepository::class);
        $client->loginUser($userRepository->findOneBy(['email' => 'admin1@admin.fr']));

        $post = $postRepository->findOneBy([]);

        $client->request('GET', '/admin/blog/update/' . $post->getId());
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

        $client->request('GET', '/admin/blog/update/' . $post->getId());
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
            '/admin/blog/create'
        ];
        yield [
            'GET',
            '/admin/blog/update/25'
        ];
    }

    public function generateBadData(): \Generator
    {
        $imgPath = dirname(__DIR__) . '/../../src/DataFixtures/images/fixture.png';
        yield [
            [
                'post[title]' => '',
                'post[summary]' => 'summary test',
                'post[content]' => 'content test',
                'post[uploadedFile]' => new UploadedFile(
                    $imgPath,
                    'fixture.png'
                ),
                'post[online]' => false,
            ],
            'This value should not be blank.'
        ];
        yield [
            [
                'post[title]' => '12345',
                'post[summary]' => 'summary test',
                'post[content]' => 'content test',
                'post[uploadedFile]' => new UploadedFile(
                    $imgPath,
                    'fixture.png'
                ),
                'post[online]' => false,
            ],
            'This value is too short. It should have 6 characters or more.'
        ];
        yield [
            [
                'post[title]' => 'title test',
                'post[summary]' => '',
                'post[content]' => 'content test',
                'post[uploadedFile]' => new UploadedFile(
                    $imgPath,
                    'fixture.png'
                ),
                'post[online]' => false,
            ],
            'This value should not be blank.'
        ];
        yield [
            [
                'post[title]' => 'title test',
                'post[summary]' => '12345678',
                'post[content]' => 'content test',
                'post[uploadedFile]' => new UploadedFile(
                    $imgPath,
                    'fixture.png'
                ),
                'post[online]' => false,
            ],
            'This value is too short. It should have 10 characters or more.'
        ];
        yield [
            [
                'post[title]' => 'title test',
                'post[summary]' => 'summary test',
                'post[content]' => '',
                'post[uploadedFile]' => new UploadedFile(
                    $imgPath,
                    'fixture.png'
                ),
                'post[online]' => false,
            ],
            'This value should not be blank.'
        ];
        yield [
            [
                'post[title]' => 'title test',
                'post[summary]' => 'summary test',
                'post[content]' => '12345678',
                'post[uploadedFile]' => new UploadedFile(
                    $imgPath,
                    'fixture.png'
                ),
                'post[online]' => false,
            ],
            'This value is too short. It should have 10 characters or more.'
        ];
    }
}