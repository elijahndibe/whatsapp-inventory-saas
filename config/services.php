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

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
        'base_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
    ],

    'whatsapp' => [
        'api_version' => env('WHATSAPP_API_VERSION', 'v20.0'),
        // Platform-level Meta app credentials — the SaaS's own Meta app,
        // not anything a business ever sees or configures. app_secret is
        // used both to verify incoming webhook signatures and to exchange
        // an Embedded Signup authorization code for a token; the others
        // are Embedded-Signup-specific. See WHATSAPP_SETUP.md.
        'app_id' => env('WHATSAPP_APP_ID'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
        'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
        // The "Configuration ID" created under App Dashboard > WhatsApp >
        // Embedded Signup — passed to FB.login() to launch the flow.
        'embedded_signup_config_id' => env('WHATSAPP_EMBEDDED_SIGNUP_CONFIG_ID'),
        // A long-lived token for a System User on the platform's own Meta
        // Business, granted whatsapp_business_management +
        // whatsapp_business_messaging once in Meta Business Settings. This
        // single token works for every business's WABA once they've
        // completed Embedded Signup (Meta shares WABA access with our
        // Business at that point) — no per-business token needed or
        // stored. See Business::whatsappAccessToken().
        'system_user_token' => env('WHATSAPP_SYSTEM_USER_TOKEN'),
        // The platform's OWN WhatsApp Business number (a Zwenko-owned
        // number, not any tenant's) — used only to send phone-verification
        // codes, since that has to work before a business/tenant even
        // exists yet to send from. Sending to a number outside an open
        // conversation window requires a Meta-approved "Authentication"
        // category message template; see WHATSAPP_SETUP.md. Left blank,
        // phone verification is treated as not configured — registration
        // and Settings fall back to not requiring it rather than blocking
        // every signup on infrastructure that was never set up.
        'platform_phone_number_id' => env('WHATSAPP_PLATFORM_PHONE_NUMBER_ID'),
        'otp_template_name' => env('WHATSAPP_OTP_TEMPLATE_NAME', 'phone_verification'),
    ],

];
