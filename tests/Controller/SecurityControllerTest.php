<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginIfSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/connexion');
        $client->submitForm('Se connecter', [
            'email' => 'fixture1@fixture.fr',
            'password' => 'fixture'
        ]);

        self::assertResponseRedirects('/', Response::HTTP_FOUND);
    }

    public function testLoginIfError(): void
    {
        $client = static::createClient();
        $client->request('GET', '/connexion');
        $client->submitForm('Se connecter', [
            'email' => 'fixture1@fixture.fr',
            'password' => 'failure'
        ]);

        self::assertResponseRedirects('/connexion', Response::HTTP_FOUND);

        $client->followRedirect();
        self::assertSelectorTextSame('.alert', 'Invalid credentials');
    }

    public function testRegistrationSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inscription');
        $client->submitForm('Inscription', [
            'registration[email]' => 'test@test.fr',
            'registration[name]' => 'test',
            'registration[plainPassword][first]' => 'test1234',
            'registration[plainPassword][second]' => 'test1234',
            'registration[age]' => '1995-12-08',
            'registration[nationality]' => 'french',
        ]);

        self::assertResponseRedirects('/', Response::HTTP_FOUND);
    }

    /**
     * @dataProvider dataFailureProvider
     */
    public function testRegistrationFail(array $formData, string $message): void
    {
        $client = static::createClient();
        $client->request('GET', '/inscription');
        $client->submitForm('Inscription', $formData);

        self::assertSelectorTextSame('ul.form__errors li', $message);
    }

    public function dataFailureProvider(): \Generator
    {
        yield [
            [
                'registration[email]' => '',
                'registration[name]' => 'test',
                'registration[plainPassword][first]' => 'test1234',
                'registration[plainPassword][second]' => 'test1234',
                'registration[age]' => '1995-12-08',
                'registration[nationality]' => 'french',
            ],
            'This value should not be blank.'
        ];
        yield [
            [
                'registration[email]' => 'test',
                'registration[name]' => 'test',
                'registration[plainPassword][first]' => 'test1234',
                'registration[plainPassword][second]' => 'test1234',
                'registration[age]' => '1995-12-08',
                'registration[nationality]' => 'french',
            ],
            'This value is not a valid email address.'
        ];
        yield [
            [
                'registration[email]' => 'fixture1@fixture.fr',
                'registration[name]' => 'test',
                'registration[plainPassword][first]' => 'test1234',
                'registration[plainPassword][second]' => 'test1234',
                'registration[age]' => '1995-12-08',
                'registration[nationality]' => 'french',
            ],
            'This value is already used.'
        ];
        yield [
            [
                'registration[email]' => 'test@test.fr',
                'registration[name]' => '',
                'registration[plainPassword][first]' => 'test1234',
                'registration[plainPassword][second]' => 'test1234',
                'registration[age]' => '1995-12-08',
                'registration[nationality]' => 'french',
            ],
            'This value should not be blank.'
        ];
        yield [
            [
                'registration[email]' => 'test@test.fr',
                'registration[name]' => 'z',
                'registration[plainPassword][first]' => 'test1234',
                'registration[plainPassword][second]' => 'test1234',
                'registration[age]' => '1995-12-08',
                'registration[nationality]' => 'french',
            ],
            'This value is too short. It should have 2 characters or more.'
        ];
        yield [
            [
                'registration[email]' => 'test@test.fr',
                'registration[name]' => 'testtttttttttttttttttttttt',
                'registration[plainPassword][first]' => 'test1234',
                'registration[plainPassword][second]' => 'test1234',
                'registration[age]' => '1995-12-08',
                'registration[nationality]' => 'french',
            ],
            'This value is too long. It should have 25 characters or less.'
        ];
        yield [
            [
                'registration[email]' => 'test@test.fr',
                'registration[name]' => 'test',
                'registration[plainPassword][first]' => 'test',
                'registration[plainPassword][second]' => 'test1234',
                'registration[age]' => '1995-12-08',
                'registration[nationality]' => 'french',
            ],
            'The password fields must match.'
        ];
        yield [
            [
                'registration[email]' => 'test@test.fr',
                'registration[name]' => 'test',
                'registration[plainPassword][first]' => 'test',
                'registration[plainPassword][second]' => 'test',
                'registration[age]' => '1995-12-08',
                'registration[nationality]' => 'french',
            ],
            'This value is too short. It should have 6 characters or more.'
        ];
        yield [
            [
                'registration[email]' => 'test@test.fr',
                'registration[name]' => 'test',
                'registration[plainPassword][first]' => 'test1234',
                'registration[plainPassword][second]' => 'test1234',
                'registration[age]' => '',
                'registration[nationality]' => 'french',
            ],
            'This value should not be blank.'
        ];
        yield [
            [
                'registration[email]' => 'test@test.fr',
                'registration[name]' => 'test',
                'registration[plainPassword][first]' => 'test1234',
                'registration[plainPassword][second]' => 'test1234',
                'registration[age]' => (New \DateTime())->format('Y-m-d'),
                'registration[nationality]' => 'french',
            ],
            'You must be 18 years or older.'
        ];
    }
}