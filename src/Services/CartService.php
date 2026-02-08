<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CouponRepository;

final class CartService
{
    public function __construct(
        private CartRepository $cartRepo,
        private ProductRepository $productRepo,
        private CouponRepository $couponRepo
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
                'subtotal' => "0.00",
                'discount' => "0.00",
                'total' => "0.00",
                'coupon' => null,
            ];
        }

        $items = $this->cartRepo->getCartItems($cartId);

        $subtotal = "0.00";

        $mapped = [];
        foreach ($items as $it) {
            $qty = (int)$it["quantity"];
            $price = (string)$it['price'];
            $lineTotal = number_format(((float)$price) * $qty, 2, '.', '');
            $subtotal = number_format(((float)$subtotal) + (float)$lineTotal, 2, '.', '');

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

        $couponInfo = null;
        $discount = "0.00";

        $couponId = $this->cartRepo->getCouponId($cartId);
        if($couponId !== null) {
            $coupon = $this->couponRepo->findById($couponId);

            if ($coupon && (int)$coupon['is_active'] === 1) {
                if ($coupon['expires_at'] === null || strtotime($coupon['expires_at']) > time()) {

                    $discount = $this->calcDiscount($subtotal, $coupon);

                    $couponInfo = [
                        'code'           => (string)$coupon['code'],
                        'type'           => (string)$coupon['type'],
                        'value'          => (string)$coupon['value'],
                        'min_cart_total' => (string)$coupon['min_cart_total'],
                    ];
                }
            }
        }

        $total = $this->calcTotal($subtotal, $discount);

        return [
            'cart_id' => $cartId,
            'items' => $mapped,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'coupon' => $couponInfo,
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
                'subtotal' => "0.00",
                'discount' => "0.00",
                'total' => "0.00",
                'coupon' => null,
            ];
        }

        $this->cartRepo->clearCart($cartId);

        return $this->view($sessionId);
    }

    private function calcDiscount(string $subtotal, array $coupon): string
    {
        $sub = (float)$subtotal;
        $min = (float)($coupon['min_cart_total'] ?? 0);

        // min sepet şartı sağlanmıyorsa indirim 0
        if ($min > 0 && $sub < $min) {
            return "0.00";
        }

        $type = (string)$coupon['type'];
        $val  = (float)$coupon['value'];

        if ($type === 'percentage') {
            $discount = $sub * ($val / 100.0);
        } else {
            $discount = $val;
        }

        if ($discount > $sub) {
            $discount = $sub;
        }

        return number_format($discount, 2, '.', '');
    }

    private function calcTotal(string $subtotal, string $discount): string
    {
        $t = (float)$subtotal - (float)$discount;
        if ($t < 0) $t = 0;
        return number_format($t, 2, '.', '');
    }

    public function applyCoupon(string $sessionId, string $code): array
    {
        $code = trim($code);
        if ($code === '') {
            throw new NotFoundException("INVALID_COUPON", "Kupon kodu boş olamaz");
        }

        $coupon = $this->couponRepo->findActiveByCode($code);
        if (!$coupon) {
            throw new NotFoundException("COUPON_NOT_FOUND", "Kupon geçersiz veya süresi dolmuş");
        }

        $cartId = $this->cartRepo->findCartIdBySessionId($sessionId);
        if($cartId === null){
            $cartId = $this->cartRepo->createCart($sessionId);
        }

        $view = $this->view($sessionId);
        $subtotal = $view["subtotal"];

        $min = (float)($coupon["min_cart_total"] ?? 0);
        if ($min>0 && (float)$subtotal < $min) {
            throw new NotFoundException("MIN_CART_TOTAL_NOT_MET", "Minimum sepet tutarı sağlanmıyor");
        }

        $this->cartRepo->setCoupon($cartId, (int)$coupon["id"]);

        return $this->view($sessionId);
    }

    public function removeCoupon(string $sessionId): array
    {
        $cartId = $this->cartRepo->findCartIdBySessionId($sessionId);
        if ($cartId === null) {
            return [
                "cart_id" => null,
                "items" => [],
                "subtotal" => "0.00",
                "discount" => "0.00",
                "total" => "0.00",
                "coupon" => null,
            ];
        }

        $this->cartRepo->removeCoupon($cartId);

        return $this->view($sessionId);
    }
}