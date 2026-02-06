<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\SessionManager;
use App\Helpers\Validator;
use App\Services\CartService;

final class CartController
{
    public function __construct(
        private Request $request,
        private CartService $cartService
    ) {}

    // GET /api/cart
    public function show(): void
    {
        $sessionId = SessionManager::getSessionId($this->request);
        $data = $this->cartService->view($sessionId);

        Response::jsonSuccess($data, 'Sepet');
    }

    // POST /api/cart/items
    public function addItem(): void
    {
        $sessionId = SessionManager::getSessionId($this->request);
        $body = $this->request->json();

        $productId = Validator::int($body['product_id'] ?? null, 'product_id', 1);
        $quantity  = Validator::int($body['quantity'] ?? null, 'quantity', 1, 999);

        $data = $this->cartService->addItem($sessionId, $productId, $quantity);

        Response::jsonSuccess($data, 'Ürün sepete eklendi', 201);
    }

    // PUT /api/cart/items/{id}
    public function updateItem(array $params): void
    {
        $sessionId = SessionManager::getSessionId($this->request);
        $body = $this->request->json();

        $cartItemId = Validator::int($params['id'] ?? null, 'id', 1);
        $quantity   = Validator::int($body['quantity'] ?? null, 'quantity', 1, 999);

        $data = $this->cartService->updateItem($sessionId, $cartItemId, $quantity);

        Response::jsonSuccess($data, 'Miktar güncellendi');
    }

    // DELETE /api/cart/items/{id}
    public function removeItem(array $params): void
    {
        $sessionId = SessionManager::getSessionId($this->request);

        $cartItemId = Validator::int($params['id'] ?? null, 'id', 1);

        $data = $this->cartService->removeItem($sessionId, $cartItemId);

        Response::jsonSuccess($data, 'Ürün sepetten çıkarıldı');
    }

    // DELETE /api/cart
    public function clear(): void
    {
        $sessionId = SessionManager::getSessionId($this->request);

        $data = $this->cartService->clear($sessionId);

        Response::jsonSuccess($data, 'Sepet temizlendi');
    }
}
