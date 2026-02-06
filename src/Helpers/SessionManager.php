<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Request;
use App\Core\Response;

final class SessionManager
{
    public static function getSessionId(Request $request): string
    {
        // Postman için header öncelikli
        $headerId = $request->header("X-Session-Id");
        if(is_string($headerId) && trim($headerId) !== "") {
            return trim($headerId);
        }

        // tarayıcı için cookie
        $cookieId = $request->cookie("session_id");
        if(is_string($cookieId) && trim($cookieId) !== "") {
            return trim($cookieId);
        }

        // yoksa üret ve cookieye yaz
        $newId = bin2hex(random_bytes(16));
        Response::setCookie("session_id", $newId);

        return $newId;
    }
}