<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\SessionManager;
use App\Helpers\Validator;
use App\Services\FavoriteService;

final class FavoriteController
{
    public function __construct(
        private Request $request,
        private FavoriteService $favoriteService
    ) {}

    // GET /api/favorites
    public function index(): void
    {
        $sessionId = SessionManager::getSessionId($this->request);
        $data = $this->favoriteService->list($sessionId);

        Response::jsonSuccess($data, "Favoriler");
    }

    // POST /api/favorites
    public function store(): void
    {
        $sessionId = SessionManager::getSessionId($this->request);
        $body = $this->request->json();

        $productId = Validator::int($body["product_id"] ?? null, "product_id" , 1);

        $this->favoriteService->add($sessionId, $productId);

        Response::jsonSuccess(null, "Favorilere eklendi", 201);
    }

    // DELETE /api/favorites/{product_id}
    public function destroy(array $params): void
    {
        $sessionId = SessionManager::getSessionId($this->request);
        $productId = Validator::int($params["product_id"] ?? null, "product_id" , 1);

        $this->favoriteService->remove($sessionId, $productId);

        Response::jsonSuccess(null, "Favorilerden çıkarıldı");
    }

    // POST /api/favorites/{product_id}/add-to-cart
    public function addToCart(array $params): void
    {
        $sessionId = SessionManager::getSessionId($this->request);
        $productId = Validator::int($params["product_id"] ?? null, "product_id", 1);

        $this->favoriteService->addFavoriteToCart($sessionId, $productId);

        Response::jsonSuccess(null, "Favori ürün sepete eklendi", 201);
    }
}