<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\AdminRepository;
use PDO;

class Auth
{
    public static function attempt(PDO $pdo, string $username, string $password): bool
    {
        $admin = AdminRepository::findByUsername($pdo, $username);

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['admin'] = [
            'id' => (int) $admin['id'],
            'username' => $admin['username'],
        ];

        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['admin']['id'], $_SESSION['admin']['username']);
    }

    public static function user(): ?array
    {
        return self::check() ? $_SESSION['admin'] : null;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin']);
        session_regenerate_id(true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Please log in to continue.');
            redirect('/admin/login');
        }
    }
}
