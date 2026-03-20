<?php

return [
    'app_name' => 'Right to Care Zambia Digital Business Card System',
    'organisation_name' => 'Right to Care Zambia',
    'base_url' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Lusaka',
    'session_name' => getenv('APP_SESSION_NAME') ?: 'rtc_zambia_cards_admin',
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
];
