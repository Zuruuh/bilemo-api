<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientTest extends KernelTestCase
{
    const USERNAME = "client_username";
    const PASSWORD = "client_password";
    const EMAIL    = "client_email";

    public function test_setters(UserPasswordHasherInterface $hasher): void
    {
        // * Initialization
        $client = new Client();
        $password = $hasher->hashPassword($client, self::PASSWORD);

        $client->setUsername(self::USERNAME)
            ->setPassword($password)
            ->setEmail(self::EMAIL);

        // * Tests

        $this->assertTrue($client->getUsername === self::USERNAME);
        $this->assertTrue(
            $hasher->isPasswordValid($client, self::PASSWORD)
        );
        $this->assertTrue($client->getEmail === self::EMAIL);
    }
}
