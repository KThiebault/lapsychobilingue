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
        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            'email' => 'fixture1@fixture.fr',
            'password' => 'failure'
        ]);

        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);

        $client->followRedirect();
        $this->assertSelectorTextSame('.alert', 'Invalid credentials');
    }
}