<?php

namespace App\Controller\Api;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AuthController extends AbstractController
{
    // private SerializerInterface $serializer;
    private AuthService $auth_service;

    public function __construct(
        // SerializerInterface $serializer,
        AuthService $auth_service
    ) {
        // $this->serializer = $serializer;
        $this->auth_service = $auth_service;
    }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $json = $this->auth_service->decode($request);

            return $this->auth_service->login($json);
        } catch (HttpException $exception) {
            $code = $exception->getStatusCode();

            return $this->json(
                [
                    'message' => $exception->getMessage(),
                    "code" => $code
                ],
                $code,
                $exception->getHeaders()
            );
        }
    }
}
