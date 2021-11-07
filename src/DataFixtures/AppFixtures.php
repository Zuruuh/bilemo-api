<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    const PRODUCT_AMOUNT = 110;
    const CLIENT_AMOUNT = 15;
    const USERS_PER_CLIENT = 35;

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $em)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < self::CLIENT_AMOUNT; ++$i) {
            $dev = $i === 0;
            $client = new Client();
            $password = $this->hasher->hashPassword($client, 'password');

            $client
                ->setUsername($dev ? 'admin' : $faker->name())
                ->setEmail($faker->email())
                ->setPassword($password);
            $em->persist($client);
            $em->flush();

            for ($j = 0; $j < self::USERS_PER_CLIENT; ++$j) {
                $user = (new User())
                    ->setClient($client)
                    ->setName($faker->name())
                    ->setBalance($faker->numberBetween(0, 1000));
                $em->persist($user);
            }
            $em->flush();
        }

        for ($i = 0; $i < self::PRODUCT_AMOUNT; ++$i) {
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
