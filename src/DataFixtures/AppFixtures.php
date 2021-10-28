<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $em)
    {
        $faker = Factory::create('fr_FR');

        $client = new Client();
        $password = $this->hasher->hashPassword($client, 'admin');

        $client
            ->setUsername('admin')
            ->setEmail('younesziadi@outlook.fr')
            ->setPassword($password);

        for ($i = 0; $i < 110; ++$i) {
            $product = (new Product())
                ->setName($faker->word())
                ->setOs($faker->word())
                ->setManufacturer($faker->word() . " " . $faker->word())
                ->setStorage($faker->numberBetween(2, 256))
                ->setPrice($faker->numberBetween(8000, 200000))
                ->setStock($faker->numberBetween(0, 32));

            $em->persist($product);
        }

        $em->flush();

        $em->persist($client);
        $em->flush();
    }
}
