<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * @return array{items: array<int, array>, total: int}
     */
    public function search(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search OR p.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }
        if ($filters['min_price'] !== null) {
            $where[] = "p.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        if ($filters['max_price'] !== null) {
            $where[] = "p.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $orderBy = match ($filters['sort']) {
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'name_desc' => 'p.name DESC',
            default => 'p.name ASC', // name_asc default
        };

        $limit = (int)$filters['limit'];
        $offset = ((int)$filters['page'] - 1) * $limit;

        // total
        $countStmt = $this->pdo->prepare("
            SELECT COUNT(*) AS cnt
            FROM products p
            $whereSql
        ");
        $countStmt->execute($params);
        $total = (int)($countStmt->fetch()['cnt'] ?? 0);

        // items
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN categories c ON c.id = p.category_id
            $whereSql
            ORDER BY $orderBy
            LIMIT :limit OFFSET :offset
        ");

        // LIMIT/OFFSET bind: PDO'da int bind etmek daha güvenli
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $items = $stmt->fetchAll();

        return ['items' => $items, 'total' => $total];
    }
}
