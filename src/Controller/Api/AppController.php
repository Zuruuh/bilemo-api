<?php

namespace App\Controller\Api;

use App\Service\DocsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AppController extends AbstractController
{
    private DocsService $docs_service;

    public function __construct(
        DocsService $docs_service
    ) {
        $this->docs_service = $docs_service;
    }

    public function index(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Welcome to Bilemo Api. If you need any help getting started, you can request the /api/docs endpoint for more informations !'
        ], 200);
    }

    #[Route('/docs/{doc}', methods: ['GET'])]
    public function doc(mixed $doc = 'app'): Response
    {
        return $this->docs_service->getHtmlContent((string) $doc);
    }
}
