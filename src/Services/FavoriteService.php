<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\FavoriteRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CartRepository;

final class FavoriteService
{
    public function __construct(
        private FavoriteRepository $favoriteRepo,
        private ProductRepository $productRepo,
        private CartRepository $cartRepo
    ) {}

    // favori listesini getir
    public function list(string $sessionId): array
    {
        $rows = $this->favoriteRepo->listBySession($sessionId);

        return array_map(function ($r) {
            return [
                "product_id" => (int)$r["product_id"],
                "name" => (string)$r["name"],
                "price" => (string)$r["price"],
                "image_url" => $r["image_url"],
                "created_at" => $r["created_at"],
            ];
        }, $rows);
    }

    // favoriye ekle
    public function add(string $sessionId, int $productId): void
    {
        $product = $this->productRepo->findById($productId);
        if(!$product) {
            throw new NotFoundException("PRODUCT_NOT_FOUND", "Ürün bulunamadı");
        }

        $this->favoriteRepo->add($sessionId, $productId);
    }

    // favoriden çıkar
    public function remove(string $sessionId, int $productId): void
    {
        $this->favoriteRepo->remove($sessionId, $productId);
    }

    // favoriyi direkt sepete ekle
    public function addFavoriteToCart(string $sessionId, int $productId): void
    {
        // favoride varmı ?
        if (!$this->favoriteRepo->exists($sessionId, $productId)){
            throw new NotFoundException("FAVORITE_NOT_FOUND", "Favoride böyle bir ürün yok");
        }

        // ürün varmı
        $product = $this->productRepo->findById($productId);
        if (!$product) {
            throw new NotFoundException("PRODUCT_NOT_FOUND", "Ürün bulunamadı");
        }

        // cart yoksa oluştur
        $cartId = $this->cartRepo->findCartIdBySessionId($sessionId);
        if ($cartId === null) {
            $cartId = $this->cartRepo->createCart($sessionId);
        }

        // sepete ekle
        $this->cartRepo->addItem($cartId, $productId, 1);
    }
}