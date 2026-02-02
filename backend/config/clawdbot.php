<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ClawDBot Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for the ClawDBot automation system.
    | You can customize various aspects of the bot behavior here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Bot Status
    |--------------------------------------------------------------------------
    |
    | Enable or disable the ClawDBot system globally.
    |
    */
    'enabled' => env('CLAWDBOT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging levels and channels for ClawDBot.
    |
    */
    'log_level' => env('CLAWDBOT_LOG_LEVEL', 'info'),
    'log_channel' => 'clawdbot',

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure queue settings for ClawDBot jobs.
    |
    */
    'queue' => [
        'connection' => env('CLAWDBOT_QUEUE_CONNECTION', 'database'),
        'notifications' => 'clawdbot-notifications',
        'reports' => 'clawdbot-reports',
        'maintenance' => 'clawdbot-maintenance'
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notification channels and settings.
    |
    */
    'notifications' => [
        'email' => env('CLAWDBOT_EMAIL_ENABLED', true),
        'sms' => env('CLAWDBOT_SMS_ENABLED', false),
        'push' => env('CLAWDBOT_PUSH_ENABLED', true),
        'database' => true,
        'rate_limit' => [
            'per_minute' => 10,
            'per_hour' => 100
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduler Settings
    |--------------------------------------------------------------------------
    |
    | Configure the Laravel scheduler for ClawDBot.
    |
    */
    'scheduler' => [
        'enabled' => env('CLAWDBOT_SCHEDULER_ENABLED', true),
        'maintenance_window' => env('CLAWDBOT_MAINTENANCE_WINDOW', '02:00-04:00'),
        'timezone' => env('CLAWDBOT_TIMEZONE', config('app.timezone')),
        'overlap_protection' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure performance and resource limits.
    |
    */
    'performance' => [
        'batch_size' => env('CLAWDBOT_BATCH_SIZE', 100),
        'timeout' => env('CLAWDBOT_TIMEOUT', 300),
        'memory_limit' => env('CLAWDBOT_MEMORY_LIMIT', '512M'),
        'max_retries' => 3,
        'retry_delay' => 60 // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Property Management Settings
    |--------------------------------------------------------------------------
    |
    | Configure property automation settings.
    |
    */
    'property_management' => [
        'expiry_warning_days' => [7, 3],
        'inactive_days' => 30,
        'cleanup_days' => 90,
        'validation_enabled' => true,
        'suspicious_detection' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Settings
    |--------------------------------------------------------------------------
    |
    | Configure analytics and reporting settings.
    |
    */
    'analytics' => [
        'retention_days' => 365,
        'real_time_enabled' => true,
        'cache_ttl' => 3600, // seconds
        'export_formats' => ['json', 'csv', 'xml']
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Integration Settings
    |--------------------------------------------------------------------------
    |
    | Configure AI service integration settings.
    |
    */
    'ai' => [
        'enabled' => env('CLAWDBOT_AI_ENABLED', false),
        'service' => env('CLAWDBOT_AI_SERVICE', 'openai'),
        'api_key' => env('CLAWDBOT_AI_API_KEY'),
        'model' => env('CLAWDBOT_AI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('CLAWDBOT_AI_MAX_TOKENS', 4000),
        'temperature' => env('CLAWDBOT_AI_TEMPERATURE', 0.7),
        'logging_enabled' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security and access control settings.
    |
    */
    'security' => [
        'admin_only_commands' => [
            'clawdbot:system-maintenance',
            'clawdbot:manual-trigger'
        ],
        'rate_limiting' => [
            'api_calls' => 1000,
            'command_triggers' => 10
        ],
        'ip_whitelist' => env('CLAWDBOT_IP_WHITELIST', ''),
        'audit_trail' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configure storage paths for ClawDBot files.
    |
    */
    'storage' => [
        'logs_path' => storage_path('app/clawdbot/logs'),
        'reports_path' => storage_path('app/clawdbot/reports'),
        'cache_path' => storage_path('app/clawdbot/cache'),
        'temp_path' => storage_path('app/clawdbot/temp')
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Templates
    |--------------------------------------------------------------------------
    |
    | Configure email template settings.
    |
    */
    'email' => [
        'logo_url' => env('CLAWDBOT_EMAIL_LOGO', ''),
        'brand_name' => env('CLAWDBOT_BRAND_NAME', 'MyProperty-RL'),
        'support_email' => env('CLAWDBOT_SUPPORT_EMAIL', 'support@myproperty.com'),
        'footer_text' => 'This is an automated message from the ClawDBot system.'
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Settings
    |--------------------------------------------------------------------------
    |
    | Configure debug and development settings.
    |
    */
    'debug' => [
        'enabled' => env('CLAWDBOT_DEBUG', false),
        'verbose_logging' => env('CLAWDBOT_VERBOSE', false),
        'dry_run_default' => false,
        'preview_emails' => false
    ]
];
