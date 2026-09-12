<?php

return [
    'login' => [
        'max_attempts' => env('ADMIN_LOGIN_MAX_ATTEMPTS', 5),
        'decay_seconds' => env('ADMIN_LOGIN_DECAY_SECONDS', 900),
        'identity_max_attempts' => env('ADMIN_LOGIN_IDENTITY_MAX_ATTEMPTS', 10),
        'identity_decay_seconds' => env('ADMIN_LOGIN_IDENTITY_DECAY_SECONDS', 3600),
        'ip_max_attempts' => env('ADMIN_LOGIN_IP_MAX_ATTEMPTS', 20),
        'ip_decay_seconds' => env('ADMIN_LOGIN_IP_DECAY_SECONDS', 900),
        'suspicious_attempts' => env('ADMIN_LOGIN_SUSPICIOUS_ATTEMPTS', 10),
    ],

    'password_reset' => [
        'max_attempts' => env('ADMIN_PASSWORD_RESET_MAX_ATTEMPTS', 3),
        'decay_minutes' => env('ADMIN_PASSWORD_RESET_DECAY_MINUTES', 15),
    ],

    'verification' => [
        'max_attempts' => env('ADMIN_VERIFICATION_MAX_ATTEMPTS', 3),
        'decay_minutes' => env('ADMIN_VERIFICATION_DECAY_MINUTES', 10),
    ],

    'password_confirmation' => [
        'max_attempts' => env('ADMIN_PASSWORD_CONFIRMATION_MAX_ATTEMPTS', 5),
        'decay_minutes' => env('ADMIN_PASSWORD_CONFIRMATION_DECAY_MINUTES', 10),
    ],

    'sensitive_operations' => [
        'max_attempts' => env('ADMIN_SENSITIVE_OPERATION_MAX_ATTEMPTS', 10),
        'decay_minutes' => env('ADMIN_SENSITIVE_OPERATION_DECAY_MINUTES', 1),
    ],
];
