<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CategoryRepository
{
    public function __construct(private PDO $pdo) {}

    public function all(): array
    {
        $stmt = $this->pdo->query("SELECT id, name, slug, created_at FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }
}
