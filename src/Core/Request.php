<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{

    #HTTP method'u döndürür
    public function method(): string
    {
        return strtoupper($_SERVER["REQUEST_METHOD"] ?? "GET");
    }

    #URL path'i döndürür
    public function path(): string
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

        if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir));
            if ($path === '') {
                $path = '/';
            }
        }
        # / trimleme
        return rtrim($path, "/") ?: "/";
    }

    # parametreleri alma
    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    # header http ile başlayan formattan alınır
    public function header(string $name, mixed $default = null): mixed
    {
        $key = "HTTP_" . strtoupper(str_replace("-", "_", $name));
        return $_SERVER[$key] ?? $default;
    }

    #json body okuma istekler burda okunur
    public function json(): array
    {
        $raw = file_get_contents("php://input");

        if ($raw === false || trim($raw) === "") {
            return [];
        }

        $data = json_decode($raw, true);
        
        #istekte hata varsa null döndür
        return is_array($data) ? $data : [];
    }
}