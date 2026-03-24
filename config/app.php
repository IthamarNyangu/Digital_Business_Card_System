<?php

return [
    'app_name' => 'RTCZ Bulk QR Contact Generator',
    'organisation_name' => 'Right to Care Zambia',
    'base_url' => rtrim(getenv('APP_URL') ?: 'http://160.242.60.31/Digital_Business_Card_System/public', '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Lusaka',
    'session_name' => getenv('APP_SESSION_NAME') ?: 'rtc_zambia_cards_admin',
    'session_cookie_secure' => filter_var(
        getenv('APP_SESSION_COOKIE_SECURE') ?: (
            (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        ),
        FILTER_VALIDATE_BOOL
    ),
    'session_cookie_samesite' => getenv('APP_SESSION_COOKIE_SAMESITE') ?: 'Lax',
    'session_timeout_seconds' => max(60, (int) (getenv('APP_SESSION_TIMEOUT_SECONDS') ?: 300)),
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
];
