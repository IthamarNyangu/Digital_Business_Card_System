<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function input(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');

        return '<input type="hidden" name="_token" value="' . $token . '">';
    }

    public static function verify(?string $token): bool
    {
        return is_string($token) && hash_equals(self::token(), $token);
    }

    public static function ensure(): void
    {
        if (!self::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            echo 'Invalid or expired form token. Please refresh the page and try again.';
            exit;
        }
    }
}
