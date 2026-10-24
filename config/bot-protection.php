<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bot Protection Master Switch
    |--------------------------------------------------------------------------
    | Enable or disable automated bot detection and scanning mitigation.
    */
    'enabled' => env('BOT_PROTECTION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Maximum allowed HTTP requests per minute per IP address.
    */
    'rate_limit' => env('BOT_PROTECTION_RATE_LIMIT', 60),
    'rate_limit_decay_minutes' => 1,

    /*
    |--------------------------------------------------------------------------
    | Whitelisted IPs
    |--------------------------------------------------------------------------
    | List of IP addresses that bypass all bot detection and rate limits.
    */
    'whitelist_ips' => [
        // '127.0.0.1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permanent Blacklisted IPs
    |--------------------------------------------------------------------------
    | IPs that receive an immediate 403 Forbidden response.
    */
    'blacklisted_ips' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Known Malicious Bot / Scraper Signatures
    |--------------------------------------------------------------------------
    | Substrings matched case-insensitively against incoming User-Agent headers.
    */
    'bot_patterns' => [
        'curl',
        'wget',
        'python-requests',
        'python/urllib',
        'scrapy',
        'bot',
        'spider',
        'crawler',
        'headless',
        'phantom',
        'selenium',
        'webdriver',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legitimate Search Engine & Social Crawlers Whitelist
    |--------------------------------------------------------------------------
    | Verified bots permitted without triggering false positives.
    */
    'whitelist_agents' => [
        // Yandex Bots
        'yandex',
        'yandexbot',
        'yandexwebmaster',
        'yandexpagechecker',
        'yandeximages',
        'yandeximage',
        'yandexmarket',
        'yandexmedia',
        'yandexvideo',
        'yandexverticals',
        'yandexsitelinks',
        'yandexsprav',
        'yandexspravbot',
        'yandexaccessibility',
        'yandexaccessibilitybot',
        'yandexonto',
        'yandexontodb',
        // Google Bots
        'googlebot',
        'google-inspectiontool',
        'googleother',
        'google-extended',
        // Bing
        'bingbot',
        'bingpreview',
        'msnbot',
        // Other engines
        'yahoo',
        'slurp',
        'duckduckbot',
        'baiduspider',
        // Social Media & Messaging Preview Cards
        'facebot',
        'facebookexternalhit',
        'twitterbot',
        'linkedinbot',
        'pinterest',
        'applebot',
        'whatsapp',
        'telegrambot',
        'skypeuripreview',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Route Restrictions
    |--------------------------------------------------------------------------
    | Routes where unconventional HTTP methods trigger strike violations.
    */
    'sensitive_routes' => [
        '/' => ['GET', 'HEAD'],
        '/index.php' => ['GET', 'HEAD'],
        '/api' => ['GET', 'HEAD'],
        '/admin' => ['GET', 'HEAD'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Strike System Threshold
    |--------------------------------------------------------------------------
    | Number of violations before an IP is automatically blacklisted for 24h.
    */
    'strike_threshold' => 3,

    /*
    |--------------------------------------------------------------------------
    | Secret & Credential Scanning Auto-Ban
    |--------------------------------------------------------------------------
    | Immediate 403 blocking and auto-blacklisting when malicious bots scan for
    | sensitive files (.env, .git, .aws, credentials.json, phpinfo, backups).
    */
    'block_credential_scans' => env('BOT_PROTECTION_BLOCK_CREDENTIAL_SCANS', true),
    'credential_scan_regex' => '/(\.env|\.git|\.aws|\.ssh|credentials\.json|firebase.*\.json|service-account.*\.json|phpinfo|\.bak|\.old|\.save)/i',

    /*
    |--------------------------------------------------------------------------
    | Challenge Mode
    |--------------------------------------------------------------------------
    | Options: 'off', 'medium', 'hard', 'under_attack'
    */
    'challenge_mode' => env('BOT_CHALLENGE_MODE', 'medium'),
];
