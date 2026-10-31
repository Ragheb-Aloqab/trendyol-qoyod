<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'trendyol' => [
        'base_url' => env('TRENDYOL_BASE_URL', 'https://apigw.trendyol.com'),
        'supplier_id' => env('TRENDYOL_SUPPLIER_ID'),
        'api_key' => env('TRENDYOL_API_KEY'),
        'api_secret' => env('TRENDYOL_API_SECRET'),
        'approved_statuses' => explode(',', env('TRENDYOL_APPROVED_STATUSES', 'Shipped,Delivered')),
        'integration_ref' => env('TRENDYOL_INTEGRATION_REF'),
        'token' => env('TRENDYOL_TOKEN'),
        'timeout' => (int) env('TRENDYOL_TIMEOUT', 30),
        'retry_attempts' => (int) env('TRENDYOL_RETRY_ATTEMPTS', 3),
        'retry_delay_ms' => (int) env('TRENDYOL_RETRY_DELAY_MS', 1000),
    ],

    'qoyod' => [
        'base_url' => env('QOYOD_BASE_URL', 'https://api.qoyod.com/2.0'),
        'api_key' => env('QOYOD_API_KEY'),
        'default_inventory_id' => env('QOYOD_DEFAULT_INVENTORY_ID'),
        'default_category_id' => env('QOYOD_DEFAULT_CATEGORY_ID'),
        'default_unit_type_id' => env('QOYOD_DEFAULT_UNIT_TYPE_ID'),
        'default_tax_id' => env('QOYOD_DEFAULT_TAX_ID', 1), // 1 = 15% VAT
        'default_sales_account_id' => env('QOYOD_DEFAULT_SALES_ACCOUNT_ID'),
        'timeout' => (int) env('QOYOD_TIMEOUT', 30),
        'retry_attempts' => (int) env('QOYOD_RETRY_ATTEMPTS', 3),
        'retry_delay_ms' => (int) env('QOYOD_RETRY_DELAY_MS', 1000),
    ],

];
