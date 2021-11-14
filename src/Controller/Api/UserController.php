<?php

namespace App\Controller\Api;

use App\Form\UserFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/users')]
class UserController extends AbstractController implements ProtectedRoute
{
    private UserService $user_service;

    public function __construct(
        UserService $user_service
    ) {
        $this->user_service = $user_service;
    }

    #[Route('/', methods: ['GET'])]
    public function getMany(Request $request): JsonResponse
    {
        return $this->user_service->getOwnPaginatedUsers($request);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(Request $request, mixed $id): JsonResponse
    {
        return $this->user_service->getOne($request, (int) $id);
    }

    #[Route('/create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $form = $this->createForm(UserFormType::class);

        return $this->user_service->create($request, $form);
    }

    #[Route('/edit/{id}', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, mixed $id): JsonResponse
    {
        $user = $this->user_service->exists($id, false);
        $form = $this->createForm(UserFormType::class, $user);

        return $this->user_service->edit($request, $form, (int) $id);
    }

    #[Route('/delete/{id}', methods: 'DELETE')]
    public function delete(Request $request, mixed $id): Response
    {
        return $this->user_service->delete($request, (int) $id);
    }
}
