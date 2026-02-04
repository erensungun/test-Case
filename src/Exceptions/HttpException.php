<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class HttpException extends Exception
{
    public function __construct(
        public readonly string $errorCode,
        public readonly int $statusCode,
        string $message
    ) {
        parent::__construct($message);
    }
}