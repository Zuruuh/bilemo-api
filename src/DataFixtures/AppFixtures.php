<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager)
    {
        $client = new Client();
        $password = $this->hasher->hashPassword($client, 'admin');

        $client
            ->setUsername('admin')
            ->setEmail('younesziadi@outlook.fr')
            ->setPassword($password);

        $manager->persist($client);
        $manager->flush();
    }
}
