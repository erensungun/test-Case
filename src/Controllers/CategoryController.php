<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\CategoryService;

final class CategoryController
{
    public function __construct(
        private Request $request,
        private CategoryService $service
    ) {}

    public function index(): void
    {
        $data = $this->service->list();
        Response::jsonSuccess($data, 'Kategori listesi');
    }
}
