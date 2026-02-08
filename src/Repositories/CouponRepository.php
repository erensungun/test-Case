<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CouponRepository
{
    public function __construct(private PDO $pdo) {}

    public function findActiveByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM coupons
            WHERE code = :code
                AND is_active = 1
                AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1
        ");

        $stmt->execute(["code" => $code]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM coupons
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute(["id" => $id]);

        $row = $stmt->fetch();
        return $row ?: null;
    }
}