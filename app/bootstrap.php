<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Load our own application classes without depending on Composer for local code.
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

$composerAutoload = BASE_PATH . '/vendor/autoload.php';

// Vendor packages are optional until Composer dependencies are installed.
if (is_file($composerAutoload)) {
    require $composerAutoload;
}

require BASE_PATH . '/app/Support/helpers.php';

$GLOBALS['config'] = [
    'app' => require BASE_PATH . '/config/app.php',
    'database' => require BASE_PATH . '/config/database.php',
];

date_default_timezone_set(config('app.timezone', 'Africa/Lusaka'));

if (session_status() === PHP_SESSION_NONE) {
    session_name(config('app.session_name', 'rtc_zambia_cards_admin'));
    session_start();
}
