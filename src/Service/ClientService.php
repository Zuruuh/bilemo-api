<?php

namespace App\Service;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientService
{
    private ClientRepository $client_repo;
    private UserPasswordHasherInterface $hasher;

    public function __construct(
        ClientRepository $client_repo,
        UserPasswordHasherInterface $hasher
    ) {
        $this->client_repo = $client_repo;
        $this->hasher = $hasher;
    }

    /**
     * Returns a Client from it's username
     * 
     * @param string $username The client to find's username 
     * 
     * @return Client|null
     */
    public function getClientFromUsername(string $username): Client|null
    {
        return $this->client_repo->findOneBy(["username" => $username]);
    }

    /**
     * Hashes a password using Symfony's UserPasswordHasher
     * 
     * @param Client $client   The client who's password is being hashed
     * @param string $password The client's plain password
     * 
     * @return string The hashed password
     */
    public function hashPassword(Client $client, string $password): string
    {
        return $this->hasher->hashPassword($client, $password);
    }

    /**
     * Verifies a password using Symfony's UserPasswordHasher
     * 
     * @param Client $client   The client who's password is being verified
     * @param string $password The client's plain password
     * 
     * @return bool Is the password correct
     */
    public function isPasswordValid(Client $client, string $password): bool
    {
        return $this->hasher->isPasswordValid($client, $password);
    }

    /**
     * Returns a client's details
     * 
     * @param Request $request The controller request
     * 
     * @return JsonResponse The http response containing the user's details
     */
    public function getUserDetails(Request $request): JsonResponse
    {
        $username = $request->getContent()['_auth'];
        $client = $this->client_repo->findOneByWithArray(['username' => $username])[0];
        unset($client['password']);

        return new JsonResponse(['client' => $client]);
    }
}
