<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Database;
use PDO;
use RuntimeException;

function base_path(string $path = ''): string
{
    if ($path === '') {
        return BASE_PATH;
    }

    return BASE_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

function config(string $key, mixed $default = null): mixed
{
    $segments = explode('.', $key);
    $value = $GLOBALS['config'] ?? [];

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function db(): PDO
{
    return Database::connection(config('database'));
}

function url(string $path = ''): string
{
    $baseUrl = rtrim((string) config('app.base_url', ''), '/');
    $cleanPath = '/' . ltrim($path, '/');

    return $path === '' ? $baseUrl : $baseUrl . $cleanPath;
}

function redirect(string $path): never
{
    $destination = preg_match('/^https?:\/\//i', $path) ? $path : url($path);

    header('Location: ' . $destination);
    exit;
}

function current_path(): string
{
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

    // Strip the script directory so routes still work when the app is hosted in a subfolder.
    if ($scriptDirectory !== '/' && $scriptDirectory !== '.' && str_starts_with($requestUri, $scriptDirectory)) {
        $requestUri = substr($requestUri, strlen($scriptDirectory));
    }

    $path = '/' . trim($requestUri, '/');

    return $path === '//' ? '/' : $path;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function remember_old_input(array $input): void
{
    unset($input['_token']);
    $_SESSION['_old'] = $input;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function set_validation_errors(array $errors): void
{
    $_SESSION['_errors'] = $errors;
}

function old(string $key, mixed $default = ''): mixed
{
    return $GLOBALS['view_old_input'][$key] ?? $default;
}

function validation_error(string $field): ?string
{
    return $GLOBALS['view_errors'][$field] ?? null;
}

function has_validation_errors(): bool
{
    return !empty($GLOBALS['view_errors'] ?? []);
}

function csrf_field(): string
{
    return Csrf::input();
}

function render(string $view, array $data = [], string $layout = 'admin', int $statusCode = 200): void
{
    http_response_code($statusCode);

    $viewFile = base_path('app/Views/' . $view . '.php');
    $layoutFile = base_path('app/Views/layouts/' . $layout . '.php');

    if (!is_file($viewFile)) {
        throw new RuntimeException('View not found: ' . $view);
    }

    if (!is_file($layoutFile)) {
        throw new RuntimeException('Layout not found: ' . $layout);
    }

    // Persisted form data and errors are loaded once after a redirect, then cleared.
    $GLOBALS['view_old_input'] = $_SESSION['_old'] ?? [];
    $GLOBALS['view_errors'] = $_SESSION['_errors'] ?? [];

    unset($_SESSION['_old'], $_SESSION['_errors']);

    $successMessage = flash('success');
    $errorMessage = flash('error');
    $warningMessage = flash('warning');

    extract($data, EXTR_SKIP);

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    require $layoutFile;

    unset($GLOBALS['view_old_input'], $GLOBALS['view_errors']);
}
