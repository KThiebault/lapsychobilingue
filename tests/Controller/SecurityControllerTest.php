<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginIfSuccess()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            'email' => 'fixture1@fixture.fr',
            'password' => 'fixture'
        ]);

        $this->assertResponseRedirects('/', Response::HTTP_FOUND);
    }

    public function testLoginIfError()
    {
        $client = static::createClient();
        $client->request('GET', '/connexion');
        $client->submitForm('Se connecter', [
            'email' => 'fixture1@fixture.fr',
            'password' => 'failure'
        ]);

        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);

        $client->followRedirect();
        $this->assertSelectorTextSame('.alert', 'Invalid credentials');
    }

    public function testRegistrationSuccess()
    {
        $client = static::createClient();
        $client->request('GET', '/inscription');
        $client->submitForm('Inscription', [
            'email' => 'test@test.fr',
            'name' => 'Robot',
            'password' => 'test1234',
            'confirmation_password' => 'test1234',
            'age' => '25',
            'nationality' => 'french'
        ]);

        $this->assertResponseRedirects('/mon-compte', Response::HTTP_FOUND);
    }
}