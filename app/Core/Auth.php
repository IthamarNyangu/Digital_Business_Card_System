<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\AdminRepository;
use PDO;

class Auth
{
    private static ?bool $checked = null;

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
        $_SESSION['admin_last_activity_at'] = time();
        self::$checked = true;

        return true;
    }

    public static function check(): bool
    {
        if (self::$checked !== null) {
            return self::$checked;
        }

        if (!isset($_SESSION['admin']['id'], $_SESSION['admin']['username'])) {
            self::$checked = false;
            return false;
        }

        $timeoutSeconds = self::timeoutSeconds();
        $lastActivityAt = (int) ($_SESSION['admin_last_activity_at'] ?? 0);

        if ($lastActivityAt > 0 && (time() - $lastActivityAt) > $timeoutSeconds) {
            self::expire();
            self::$checked = false;

            return false;
        }

        self::touch();
        self::$checked = true;

        return true;
    }

    public static function user(): ?array
    {
        return self::check() ? $_SESSION['admin'] : null;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin']);
        unset($_SESSION['admin_last_activity_at']);
        session_regenerate_id(true);
        self::$checked = false;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            if (!isset($_SESSION['_flash']['warning'])) {
                flash('error', 'Please log in to continue.');
            }
            redirect('/admin/login');
        }
    }

    private static function timeoutSeconds(): int
    {
        return max(60, (int) config('app.session_timeout_seconds', 900));
    }

    private static function touch(): void
    {
        $_SESSION['admin_last_activity_at'] = time();
    }

    private static function expire(): void
    {
        unset($_SESSION['admin']);
        unset($_SESSION['admin_last_activity_at']);
        session_regenerate_id(true);
        flash('warning', self::timeoutMessage());
    }

    public static function timeoutMessage(): string
    {
        $timeoutMinutes = (int) ceil(self::timeoutSeconds() / 60);

        return 'You were logged out after ' . $timeoutMinutes . ' minute' . ($timeoutMinutes === 1 ? '' : 's') . ' of inactivity.';
    }
}
