<?php 
declare(strict_types=1);

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Exceptions\HttpException;

use App\Controllers\ProductController;
use App\Controllers\CategoryController;
use App\Controllers\CartController;

use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CartRepository;

use App\Services\ProductService;
use App\Services\CategoryService;
use App\Services\CartService;

use App\Helpers\SessionManager;

#autload
require __DIR__ . "/../vendor/autoload.php";

#hataları yakalama
set_exception_handler(function (Throwable $e){
    #tanımladığımız bir hata ise özel code ile dön
    if($e instanceof HttpException) {
        Response::jsonError($e->errorCode, $e->getMessage(), $e->statusCode);
    }

    Response::jsonError("SERVER_ERROR", $e->getMessage(), 500);
});

$config = require __DIR__ . "/../config/database.php";
$db = new Database($config);

$request = new Request();
$router = new Router();

$productRepo  = new ProductRepository($db->pdo());
$categoryRepo = new CategoryRepository($db->pdo());
$cartRepo     = new CartRepository($db->pdo());

$productService  = new ProductService($productRepo);
$categoryService = new CategoryService($categoryRepo);
$cartService     = new CartService($cartRepo, $productRepo);

$productController  = new ProductController($request, $productService);
$categoryController = new CategoryController($request, $categoryService);
$cartController     = new CartController($request, $cartService);

$router->add('GET', '/ping', fn($req, $params) =>
    Response::jsonSuccess(['pong' => true], 'OK')
);

$router->add('GET', '/whoami', function($req, $params) use ($request) {
    $sid = SessionManager::getSessionId($request);
    Response::jsonSuccess(['session_id' => $sid], 'OK');
});

$router->add('GET',    '/api/cart',           fn($req, $params) => $cartController->show());
$router->add('POST',   '/api/cart/items',     fn($req, $params) => $cartController->addItem());
$router->add('PUT',    '/api/cart/items/{id}',fn($req, $params) => $cartController->updateItem($params));
$router->add('DELETE', '/api/cart/items/{id}',fn($req, $params) => $cartController->removeItem($params));
$router->add('DELETE', '/api/cart',           fn($req, $params) => $cartController->clear());

$router->add('GET', '/api/products', fn() => $productController->index());
$router->add('GET', '/api/products/{id}', fn($req, $params) => $productController->show($params));
$router->add('GET', '/api/categories', fn() => $categoryController->index());

$router->dispatch($request);