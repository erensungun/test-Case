<?php
declare(strict_types=1);

return [
    "dsn" => "mysql:host=127.0.0.1;dbname=ecommerce_api;charset=utf8mb4",
    "user" => "root",
    "password" => "",
    "options" => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,            //sql hatalarını yakalamak için
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       //fetch'ler associative array gelsin
    ],
];