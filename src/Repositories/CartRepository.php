<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CartRepository
{
    public function __construct(private PDO $pdo) {}

    // session id ile cart id bulma
    public function findCartIdBySessionId(string $sessionId): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM carts WHERE session_id = :sid LIMIT 1");
        $stmt->execute(["sid" => $sessionId]);

        $row = $stmt->fetch();
        return $row ? (int)$row["id"] : null;
    }

    // cart yoksa oluşturma
    public function createCart(string $sessionId): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO carts (session_id, coupon_id, created_at, updated_at)
            VALUES (:sid, NULL, NOW(), NOW())
        ");
        $stmt->execute(["sid" => $sessionId]);

        return (int)$this->pdo->lastInsertId();
    }

    // Cart id üzerinden itemları ürn bilgileriyle getir
    public function getCartItems(int $cartId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                ci.id AS cart_item_id,
                ci.product_id,
                ci.quantity,
                p.name,
                p.price,
                p.image_url
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            WHERE ci.cart_id = :cart_id
            ORDER BY ci.id DESC
        ");
        $stmt->execute(["cart_id" => $cartId]);

        return $stmt->fetchAll() ?: [];
    }

    // sepete ürün ekleme
    public function addItem(int $cartId, int $productId, int $quantity): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity, created_at)
            VALUES (:cart_id, :product_id, :quantity, NOW())
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
        ");

        $stmt->execute([
            "cart_id" => $cartId,
            "product_id" => $productId,
            "quantity" => $quantity,
        ]);

        // carts.updated_at güncellemesi
        $this->touchCart($cartId);
    }

    // cart item id ile tek item bulma güncelleme için
    public function findCartItemById(int $cartItemId, int $cartId): ?array
    {
        $stmt = $this->pdo->prepare("
            Select id, cart_id, product_id, quantity
            FROM cart_items
            WHERE id = :id AND cart_id = :cart_id
            LIMIT 1
        ");
        $stmt->execute(["id" => $cartItemId, "cart_id" => $cartId]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    // miktar güncelleme
    public function updateItemQuantity(int $cartItemId, int $cartId, int $quantity): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE cart_items
            SET quantity = :q
            WHERE id = :id AND cart_id = :cart_id
        ");
        $stmt->execute(["q" => $quantity, "id" => $cartItemId, "cart_id" => $cartId]);

        $this->touchCart($cartId);
    }

    // sepetten ürün silme
    public function deleteItem(int $cartItemId, int $cartId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE id = :id AND cart_id = :cart_id");
        $stmt->execute(["id" => $cartItemId, "cart_id" => $cartId]);

        $this->touchCart($cartId);
    }

    // sepeti boşatlma
    public function clearCart(int $cartId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = :cart_id");
        $stmt->execute(["cart_id" => $cartId]);

        $this->touchCart($cartId);
    }

    private function touchCart(int $cartId): void
    {
        $stmt = $this->pdo->prepare("UPDATE carts SET updated_at = NOW() WHERE id = :id");
        $stmt->execute(["id" => $cartId]);
    }
}