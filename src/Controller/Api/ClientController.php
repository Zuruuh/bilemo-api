<?php

namespace App\Controller\Api;

use App\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/client')]
class ClientController extends AbstractController implements ProtectedRoute
{
    private ClientService $client_service;

    public function __construct(
        ClientService $client_service,
    ) {
        $this->client_service = $client_service;
    }

    #[Route('/', methods: ['GET'])]
    public function me(Request $request): JsonResponse
    {
        return $this->client_service->getUserDetails($request);
    }
}
