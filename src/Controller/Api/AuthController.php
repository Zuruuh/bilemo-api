<?php

namespace App\Controller\Api;

use App\Form\LoginFormType;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AuthController extends AbstractController
{
    private AuthService $auth_service;

    public function __construct(
        AuthService $auth_service
    ) {
        $this->auth_service = $auth_service;
    }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $form = $this->createForm(LoginFormType::class);

        return $this->auth_service->login($request, $form);
    }
}
