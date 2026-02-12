<?php
/**
 * Global configuration for the Student Management API.
 *
 * This file centralizes environment-aware configuration such as
 * database credentials and basic application flags (e.g. debug mode).
 *
 * Values can be overridden using environment variables so that
 * credentials are not hard-coded in source for production:
 * - SMS_API_DB_HOST
 * - SMS_API_DB_NAME
 * - SMS_API_DB_USER
 * - SMS_API_DB_PASS
 * - SMS_API_DEBUG
 */

return [
    'db' => [
        'host'    => getenv('SMS_API_DB_HOST') ?: 'localhost',
        'name'    => getenv('SMS_API_DB_NAME') ?: 'sms_api',
        'user'    => getenv('SMS_API_DB_USER') ?: 'root',
        'pass'    => getenv('SMS_API_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        // When true, error responses may include additional debug information.
        'debug' => (bool) (getenv('SMS_API_DEBUG') ?: false),
    ],
];

