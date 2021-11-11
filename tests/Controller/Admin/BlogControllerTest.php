<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BlogControllerTest extends WebTestCase
{
    /**
     * @dataProvider generateUri
     */
    public function testGoodUrl(string $method, string $uri): void
    {
        $client = self::createClient();
        $client->request($method, $uri);

        self::assertResponseIsSuccessful();
    }

    public function testListingPost(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/admin/blog');
        $repository = $client->getContainer()->get(PostRepository::class);

        self::assertCount($repository->count([]), $crawler->filter('tbody tr'));
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

        $repository = $client->getContainer()->get(PostRepository::class);
        $post = $repository->findOneBy([]);

        $client->request('GET', '/admin/blog/' . $post->getId());
        $client->submitForm('Modifier', $data);
        self::assertResponseStatusCodeSame(302);

        $updatedPost = $repository->findOneBy(['id' => $post->getId()]);
        $client->followRedirect();

        self::assertEquals($data['post[title]'], $updatedPost->getTitle());
        self::assertEquals($data['post[summary]'], $updatedPost->getSummary());
        self::assertEquals($data['post[content]'], $updatedPost->getContent());
        self::assertEquals($data['post[online]'], $updatedPost->isOnline());
        self::assertSelectorTextSame('.flash-success', 'Le post a bien été mis à jour.');
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
}