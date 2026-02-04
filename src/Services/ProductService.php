<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\ProductRepository;

final class ProductService
{
    public function __construct(private ProductRepository $repo) {}

    public function getProduct(int $id): array
    {
        $product = $this->repo->findById($id);
        if (!$product) {
            throw new NotFoundException('PRODUCT_NOT_FOUND', 'Ürün bulunamadı');
        }
        return $product;
    }

    /**
     * @return array{items: array, meta: array}
     */
    public function listProducts(array $filters): array
    {
        $result = $this->repo->search($filters);

        $total = $result['total'];
        $page = $filters['page'];
        $limit = $filters['limit'];
        $totalPages = (int)ceil($total / $limit);

        return [
            'items' => $result['items'],
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ];
    }
}
