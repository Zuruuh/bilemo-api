<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class ProductService
{
    const HTTP_NOT_FOUND = 'This product does not exists !';

    private ProductRepository $product_repo;
    private RouterInterface   $router;

    public function __construct(
        ProductRepository $product_repo
    ) {
        $this->product_repo = $product_repo;
    }

    /**
     * Returns paginated products in a json object
     * 
     * @param Request $request The controller request
     * 
     * @return JsonResponse The http response returned to the user
     */
    public function getPaginatedProducts(Request $request): JsonResponse
    {
        $total = $this->product_repo->count([]);
        $cursor = $request->query->getInt("cursor");
        $cursor = min($cursor, $total);

        $products = $this->product_repo->findByCursor($cursor);

        foreach ($products as $product) {
            $product['_links'] = [
                'get' => [
                    'methods' => [
                        'GET'
                    ],
                    'route' => $this->router->generate(
                        'app_api_user_getone',
                        ['id' => $product['id']],
                    )
                ]
            ];
        }

        return new JsonResponse(
            [
                "products" => $products
            ],
            empty($products) ? 404 : 200 // 302: Found ?
        );
    }

    /**
     * Returns a specific product in a json object
     * 
     * @param int $id The passed in id
     * 
     * @return JsonResponse The http response returned to the user
     */
    public function getProduct(int $id): JsonResponse
    {
        $product = $this->exists($id);

        return new JsonResponse($product);
    }

    /**
     * Checks if a product exists & return it
     * 
     * @param int $id The product's id
     * 
     * @throws NotFoundHttpException if product does not exist
     * 
     * @return array $product The searched product
     */
    private function exists(int $id): array
    {
        $product = $this->product_repo->findOneByWithArray(['id' => $id]);
        if (empty($product)) {
            throw new NotFoundHttpException(self::HTTP_NOT_FOUND);
        }
        return ['product' => $product[0]];
    }
}
