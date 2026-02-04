<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private PDO $pdo;

    #DB config alır ve PDO bağlantısı kurar

    public function __construct(array $config)
    {
        $this->pdo = new PDO(
            $config["dsn"],
            $config["user"],
            $config["password"],
            $config["options"] ?? []
        );
    }

    #PDO'yu dışarıya güvenli şekilde döndüren method.

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}

