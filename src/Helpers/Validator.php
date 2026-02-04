<?php 
declare(strict_types=1);

namespace App\Helpers;

use App\Exceptions\ValidationException;

final class Validator
{
    public static function int(mixed $value, string $field, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        if($value === null || $value === "") {
            throw new ValidationException("VALIDATION_ERROR", "$field zorunludur");
        }

        if (!is_numeric($value) || (string)(int)$value !== (string)$value) {
            throw new ValidationException('VALIDATION_ERROR', "$field integer olmalıdır");
        }

        $v = (int)$value;

        if($v < $min || $v > $max){
            throw new ValidationException("VALIDATION_ERROR", "$field aralık dışı");
        }

        return $v;
    }

    public static function optionalString(mixed $value, int $maxLen = 255): ?string
    {
        if($value === null) return null;

        $v = trim((string)$value);
        if(mb_strlen($v) > $maxLen) {
            $v = mb_substr($v, 0, $maxLen);
        }
        return $v;
    }

    public static function optionalFloat(mixed $value): ?float
    {
        if($value === null || $value === "") return null;
        if(!is_numeric($value)) {
            throw new ValidationException("VALIDATION_ERROR", "Fiyat alanı sayı olmalıdır");
        }
        return (float)$value;
    }
}