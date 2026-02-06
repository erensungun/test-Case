<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

final class CartService
{
    public function __construct(
        private CartRepository $cartRepo,
        private ProductRepository $productRepo
    ) {}

    // Sepeti görüntüleme
    public function view(string $sessionId): array
    {
        $cartId = $this->cartRepo->findCartIdBySessionId($sessionId);

        // Cart hiç oluşmamışsa boş döndür
        if ($cartId === null) {
            return [
                'cart_id' => null,
                'items' => [],
                'total' => "0.00",
            ];
        }

        $items = $this->cartRepo->getCartItems($cartId);

        $total = "0.00";
        $mapped = [];
        foreach ($items as $it) {
            $qty = (int)$it["quantity"];
            $price = (string)$it['price'];
            $lineTotal = number_format(((float)$price) * $qty, 2, '.', '');
            $total = number_format(((float)$total) + (float)$lineTotal, 2, '.', '');

            $mapped[] = [
                'cart_item_id' => (int)$it['cart_item_id'],
                'product_id'   => (int)$it['product_id'],
                'name'         => (string)$it['name'],
                'price'        => $price,
                'quantity'     => $qty,
                'image_url'    => $it['image_url'],
                'line_total'   => $lineTotal,
            ];
        }

        return [
            'cart_id' => $cartId,
            'items' => $mapped,
            'total' => $total,
        ];
    }

    // Sepete ürün ekle
    public function addItem(string $sessionId, int $productId, int $quantity): array
    {
        // Ürün var mı yokmu
        $product = $this->productRepo->findById($productId);
        if (!$product) {
            throw new NotFoundException('PRODUCT_NOT_FOUND', 'Ürün bulunamadı');
        }

        // Cart yoksa oluştur
        $cartId = $this->cartRepo->findCartIdBySessionId($sessionId);
        if ($cartId === null) {
            $cartId = $this->cartRepo->createCart($sessionId);
        }

        // Ekle
        $this->cartRepo->addItem($cartId, $productId, $quantity);

        // Güncel sepeti dön
        return $this->view($sessionId);
    }

    // Miktar güncelle
    public function updateItem(string $sessionId, int $cartItemId, int $quantity): array
    {
        $cartId = $this->cartRepo->findCartIdBySessionId($sessionId);
        if ($cartId === null) {
            throw new NotFoundException('CART_NOT_FOUND', 'Sepet bulunamadı');
        }

        $item = $this->cartRepo->findCartItemById($cartItemId, $cartId);
        if (!$item) {
            throw new NotFoundException('CART_ITEM_NOT_FOUND', 'Sepet ürünü bulunamadı');
        }

        $this->cartRepo->updateItemQuantity($cartItemId, $cartId, $quantity);

        return $this->view($sessionId);
    }

    // Item sil
    public function removeItem(string $sessionId, int $cartItemId): array
    {
        $cartId = $this->cartRepo->findCartIdBySessionId($sessionId);
        if ($cartId === null) {
            throw new NotFoundException('CART_NOT_FOUND', 'Sepet bulunamadı');
        }

        $item = $this->cartRepo->findCartItemById($cartItemId, $cartId);
        if (!$item) {
            throw new NotFoundException('CART_ITEM_NOT_FOUND', 'Sepet ürünü bulunamadı');
        }

        $this->cartRepo->deleteItem($cartItemId, $cartId);

        return $this->view($sessionId);
    }

    // Sepeti temizle
    public function clear(string $sessionId): array
    {
        $cartId = $this->cartRepo->findCartIdBySessionId($sessionId);
        if ($cartId === null) {
            // Zaten yoksa boş dön
            return [
                'cart_id' => null,
                'items' => [],
                'total' => "0.00",
            ];
        }

        $this->cartRepo->clearCart($cartId);

        return $this->view($sessionId);
    }
}
