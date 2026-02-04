<?php
declare(strict_types=1);

namespace App\Exceptions;

final class NotFoundException extends HttpException
{
    public function __construct(string $code, string $message)
    {
        parent::__construct($code, 404, $message);
    }
}