<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class ProductService
{
    public const HTTP_NOT_FOUND = 'This product does not exists !';

    private ProductRepository $product_repo;
    private RouterInterface   $router;

    public function __construct(
        ProductRepository $product_repo,
        RouterInterface $router
    ) {
        $this->product_repo = $product_repo;
        $this->router = $router;
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
        $cursor = $request->query->getInt('cursor');
        $cursor = min($cursor, $total);

        $products_array = $this->product_repo->findByCursor($cursor);

        $products = array_map(function ($product) {
            $entity = $product;
            $entity['_links'] = $this->generateLinks($product['id']);

            return $entity;
        }, $products_array);

        return new JsonResponse(
            ['products' => $products],
            empty($products) ? 404 : 200 // 302: Found ?
        );
    }

    /**
     * Returns a specific product in a json object.
     * 
     * @param int $id The passed in id
     * 
     * @return JsonResponse The http response returned to the user
     */
    public function getProduct(int $id): JsonResponse
    {
        $product = $this->exists($id);
        $product['_links'] = $this->generateLinks($id);

        return new JsonResponse($product);
    }

    /**
     * Checks if a product exists & return it.
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

        return (array) $product[0];
    }

    /**
     * Generates the Hateoas entity links.
     * 
     * @param int $id The product's id
     * 
     * @return array The product's links
     */
    private function generateLinks(int $id): array
    {
        return [
            'get' => [
                'methods' => [
                    'GET'
                ],
                'route' => $this->router->generate(
                    'app_api_user_getone',
                    ['id' => $id],
                )
            ]
        ];
    }
}
