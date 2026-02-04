<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\CategoryRepository;

final class CategoryService
{
    public function __construct(private CategoryRepository $repo) {}

    public function list(): array
    {
        return $this->repo->all();
    }
}
