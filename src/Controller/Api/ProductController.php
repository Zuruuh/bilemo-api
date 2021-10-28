<?php

namespace App\Controller\Api;

use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products')]
class ProductController extends AbstractController implements ProtectedRoute
{
    private ProductService $product_service;

    public function __construct(
        ProductService $product_service
    ) {
        $this->product_service = $product_service;
    }

    #[Route('/', methods: ['GET'])]
    public function getMany(Request $request): JsonResponse
    {
        return $this->product_service->getPaginatedProducts($request);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne($id): JsonResponse
    {
        return $this->product_service->getProduct((int) $id);
    }
}
