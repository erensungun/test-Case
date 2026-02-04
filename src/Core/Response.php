<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    #başarılı response döndürme
    public static function jsonSuccess(
        mixed $data = null,
        string $message = "İşlem başarılı",
        int $status = 200
    ): void {
        http_response_code($status);
        header("Content-Type: application/json; charset=utf-8");

        echo json_encode([
            "success" => true,
            "data" => $data,
            "message" => $message
        ], JSON_UNESCAPED_UNICODE);

        #response döndükten sonra kodun devam etmemesi için exit
        exit;
    }

    #hata response döndürme
    public static function jsonError(string $code, string $message, int $status): void
    {
        http_response_code($status);
        header("Content-Type: application/json; charset=utf-8");

        echo json_encode([
            "success" => false,
            "error" => [
                "code" => $code,
                "message" => $message
            ]
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}