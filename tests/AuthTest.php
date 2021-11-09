<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthTest extends KernelTestCase
{
    public function test_authentication(): void
    {
        self::bootKernel();
        $_container = static::getContainer();

        $client = ($_container->get(HttpClientInterface::class))->withOptions([
            'base_uri' => 'https://app.bilemo',
            'verify_peer' => false,
            'verify_host' => false,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
        //$em = $_container->get(EntityManagerInterface::class);

        $login_response = $client->request(
            'POST',
            '/api/login',
            [
                'body' => json_encode([
                    'username' => 'admin',
                    'password' => 'password'
                ])
            ]
        );
        $content = json_decode($login_response->getContent());
        $this->assertTrue(isset($content->token));

        $token = $content->token;

        $me_response = $client->request(
            'GET',
            '/api/clients/me',
            [
                'headers' => [
                    'Authorization' => "Bearer $token"
                ]
            ]
        );
        $content = json_decode($me_response->getContent());

        $this->assertTrue($content->client->username === 'admin');
    }
}
