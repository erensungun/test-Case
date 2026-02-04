<?php
declare(strict_types=1);

namespace App\Exceptions;

final class ValidationException extends HttpException
{
    public function __construct(string $code, string $message)
    {
        parent::__construct($code, 422, $message);
    }
}