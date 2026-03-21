<?php

return [
    'app_name' => 'Right to Care Zambia Digital Business Card System',
    'organisation_name' => 'Right to Care Zambia',
    'base_url' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Lusaka',
    'session_name' => getenv('APP_SESSION_NAME') ?: 'rtc_zambia_cards_admin',
    'session_timeout_seconds' => max(60, (int) (getenv('APP_SESSION_TIMEOUT_SECONDS') ?: 300)),
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
];
