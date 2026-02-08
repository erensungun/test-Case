<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class FavoriteRepository
{
    public function __construct(private PDO $pdo) {}

    // listeleme
    public function listBySession(string $sessionId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                f.product_id,
                f.created_at,
                p.name,
                p.price,
                p.image_url
            FROM favorites f
            JOIN products p ON p.id = f.product_id
            WHERE f.session_id = :sid
            ORDER BY f.created_at DESC
        ");
        $stmt->execute(["sid" => $sessionId]);

        return $stmt->fetchAll() ?: [];
    }

    // favoriye ekle
    public function add(string $sessionId, int $productId):void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO favorites (session_id, product_id, created_at)
            VALUES (:sid, :pid, NOW())
            ON DUPLICATE KEY UPDATE created_at = created_at
        ");
        $stmt->execute(["sid" => $sessionId, "pid" => $productId]);
    }

    // favoriden çıkar
    public function remove(string $sessionId, int $productId): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM favorites
            WHERE session_id = :sid AND product_id = :pid
        ");
        $stmt->execute(["sid" => $sessionId, "pid" => $productId]);
    }

    // favoridemi kontrol
    public function exists(string $sessionId, int $productId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM favorites
            WHERE session_id = :sid AND product_id = :pid
            LIMIT 1
        ");
        $stmt->execute(["sid" => $sessionId, "pid" => $productId]);

        return (bool)$stmt->fetchColumn();
    }
}