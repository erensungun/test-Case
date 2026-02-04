<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $method = strtoupper($method);

        $this->routes[$method][] = [
            "pattern" => $pattern,
            "handler" => $handler,
        ];
    }

    # gelen requesti alır, uygun route'u bulur, handler'ı çalıştırır.
    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            # patterini regexe çeviriyoruz
            $regex = $this->convertPatternToRegex($route["pattern"]);

            # regex eşleşmesini kontrol etme
            if(preg_match($regex, $path, $matches)) {
                #matches named parametrelerini ayıklaması
                $params = array_filter(
                    $matches,
                    fn($k) => !is_int($k),
                    ARRAY_FILTER_USE_KEY
                );
                #handler'ı çağır
                ($route["handler"])($request, $params);
                return;
            }
        }

        #hiçbir route eşleşmediyse 404 döndür
        Response::jsonError("NOT_FOUND", "Endpoint bulunamadı", 404);
    }

    #patter'den regex'e dönüşüm
    private function convertPatternToRegex(string $pattern): string
    {
        $regex = preg_replace(
            "#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#",
            "(?P<$1>[^/]+)",
            $pattern
        );

        return "#^" . $regex . "$#";
    }
}