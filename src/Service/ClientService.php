<?php

namespace App\Service;

use App\Entity\Client;
use App\Repository\ClientRepository;

class ClientService
{
    private ClientRepository $client_repo;

    public function __construct(
        ClientRepository $client_repo
    ) {
        $this->client_repo = $client_repo;
    }

    public function getClientFromUsername(string $username): Client|null
    {
        return $this->client_repo->findOneBy(["username" => $username]);
    }
}
