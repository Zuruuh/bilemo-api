<?php

namespace App\Tests;

use App\Entity\Client;
use App\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class ClientTest extends KernelTestCase
{
    const USERNAME = "client_username";
    const PASSWORD = "client_password";
    const EMAIL    = "client_email";

    public function test_setters(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();

        $hasher = $container->get(ClientService::class);

        $client = new Client();
        $password = $hasher->hashPassword($client, self::PASSWORD);

        $client->setUsername(self::USERNAME);
        $client->setEmail(self::EMAIL);
        $client->setPassword($password);

        $this->assertTrue(
            $client->getUserIdentifier() === self::USERNAME
        );
        $this->assertTrue(
            $client->getEmail() === self::EMAIL
        );
        $this->assertTrue(
            $hasher->isPasswordValid($client, self::PASSWORD)
        );
    }
}
