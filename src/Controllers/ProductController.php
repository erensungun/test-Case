<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Validator;
use App\Services\ProductService;

final class ProductController
{
    public function __construct(
        private Request $request,
        private ProductService $service
    ) {}

    public function index(): void
    {
        // query param parse + basit validation
        $page = (int)($this->request->query('page', 1));
        if ($page < 1) $page = 1;

        $limit = (int)($this->request->query('limit', 10));
        if ($limit < 1) $limit = 10;
        if ($limit > 50) $limit = 50;

        $search = Validator::optionalString($this->request->query('search'));
        $categoryId = $this->request->query('category_id');
        $categoryId = ($categoryId !== null && $categoryId !== '') ? (int)$categoryId : null;

        $minPrice = $this->request->query('min_price');
        $minPrice = ($minPrice !== null && $minPrice !== '') ? (float)$minPrice : null;

        $maxPrice = $this->request->query('max_price');
        $maxPrice = ($maxPrice !== null && $maxPrice !== '') ? (float)$maxPrice : null;

        $sort = (string)$this->request->query('sort', 'name_asc');
        $allowedSort = ['price_asc', 'price_desc', 'name_asc', 'name_desc'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'name_asc';
        }

        $payload = $this->service->listProducts([
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'category_id' => $categoryId,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sort,
        ]);

        Response::jsonSuccess($payload, 'Ürün listesi');
    }

    public function show(array $params): void
    {
        $id = Validator::int($params['id'] ?? null, 'id', 1);
        $product = $this->service->getProduct($id);

        Response::jsonSuccess($product, 'Ürün detayı');
    }
}
