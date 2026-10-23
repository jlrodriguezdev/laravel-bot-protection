# 🛡️ Laravel BotProtection

<p align="center">
  <a href="https://packagist.org/packages/jlrodriguezdev/laravel-bot-protection"><img src="https://img.shields.io/badge/package-jlrodriguezdev%2Flaravel--bot--protection-orange.svg?style=flat-square" alt="Package"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/php-%5E8.1%20%7C%20%5E8.2%20%7C%20%5E8.3%20%7C%20%5E8.4%20%7C%20%5E8.5-blue.svg?style=flat-square" alt="PHP Version"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/laravel-%5E9.0%20%7C%20%5E10.0%20%7C%20%5E11.0-red.svg?style=flat-square" alt="Laravel Version"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-green.svg?style=flat-square" alt="License"></a>
  <a href="https://latiru.ru"><img src="https://img.shields.io/badge/tested%20in%20production-latiru.ru-purple.svg?style=flat-square" alt="Battle Tested in Production"></a>
</p>

> **High-Performance Web Application Firewall (WAF) & Anti-Bot Middleware for Laravel Applications**  
> *Battle-tested and running live in production at [LATIRU.RU](https://latiru.ru).*

---

## 🌐 Choose Your Language / Выберите язык / Elige tu idioma

* 🇪🇸 **[Leer en Español (README.es.md)](README.es.md)** — Documentación completa en Español y caso de estudio real en producción.
* 🇬🇧 **[Read in English (README.md)](#-english---laravel-botprotection)** — Full documentation in English, architecture, and production case study.
* 🇷🇺 **[Читать на русском (README.ru.md)](README.ru.md)** — Полная документация на русском языке и разбор реальных логов атак.

---

# 🇬🇧 English - Laravel BotProtection

### 1. Overview & Real Production Case Study
`BotProtection` is a zero-dependency, battle-tested Laravel middleware designed to defend web platforms against hostile automated scrapers, exploit reconnaissance bots, and scanning toolkits.

> 🚀 **Live Case Study from [LATIRU.RU](https://latiru.ru)**:  
> On **October 22, 2026**, our production platform [LATIRU.RU](https://latiru.ru) came under heavy automated scanning and reconnaissance during the global wave of **PHP2Shell (CVE-2026-109618 / CVE-2026-109619)** attacks.  
> As audited in our Laravel Telescope and daily error traces, automated attacker bots bombarded the application with exploit probes:
> * `POST /php-cgi/php-cgi.exe...` -> **Blocked with 405 / 403**
> * `POST /api/gql` and `POST /graphql/api` -> **Blocked with 405 / 403**
> * `GET /.env`, `GET /.git/config`, `GET /.vscode/sftp.json` -> **Neutralized with 404**
>
> **The Result**: The strike system accumulated the violation counts and permanently blacklisted the offending IP addresses within 3 requests. Subsequent probes to public routes (`/`, `/events`, `/calc`) were instantly dropped with **`403 Forbidden` (`errors.bot-detected`)**, protecting our PHP workers and MySQL database from exhaustion.

---

### 2. Key Features
* **Automated Scraper & Headless Tool Neutralization**: Drops traffic from headless browsers, automation toolkits, and scripted probes (`curl`, `wget`, `python-requests`, `scrapy`, `selenium`, `webdriver`, `phantomjs`).
* **Official Search Engine Whitelist**: Zero false positives for legitimate search crawlers:
  - **Google**: `googlebot`, `google-inspectiontool`, `google-extended`.
  - **Yandex**: `yandexbot`, `yandeximages`, `yandexvideo`, `yandexsitelinks`, `yandexmarket`, `yandexaccessibility`.
  - **Bing**: `bingbot`, `bingpreview`, `msnbot`.
  - **Social Previews**: TelegramBot, WhatsApp, TwitterBot, Facebot.
* **Strike-Based Permanent IP Blacklisting**: Probes sending invalid HTTP methods (`POST`, `PUT`, `DELETE`) to sensitive root routes (`/`, `/api`, `/admin`) receive strikes in Cache. Upon reaching 3 violations, the IP is blacklisted automatically with an immediate **403 Forbidden**.
* **PHP2Shell Reconnaissance Shield**: Before memory corruption or RCE chains can be staged against PHP runtimes, `BotProtection` drops the automated scanner at the application door.
* **Contextual Rate Limiting**: IP-based decay counter returning clean **429 Too Many Requests** responses under aggressive bursts.

---

### 3. Installation

#### Require with Composer:
```bash
composer require jlrodriguezdev/laravel-bot-protection
```

#### Register in `app/Http/Kernel.php`:
```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Jlrodriguezdev\BotProtection\Middleware\BotProtection::class,
    ],
];
```

#### Publish Configuration & Views:
```bash
php artisan vendor:publish --provider="Jlrodriguezdev\BotProtection\BotProtectionServiceProvider"
```

---

### 4. Configuration (`config/bot-protection.php`)
```php
return [
    'enabled' => env('BOT_PROTECTION_ENABLED', true),
    'rate_limit' => env('BOT_PROTECTION_RATE_LIMIT', 60),
    'strike_threshold' => 3,
    'whitelist_ips' => [
        '127.0.0.1',
        '::1',
        // 'your.server.public.ip', // Whitelist server's own IP so internal CLI/cron calls are never blocked
    ],
    'blacklisted_ips' => [
        //
    ],
];
```

> 💡 **Important Note on Whitelist Testing**:  
> If you test the middleware by running `curl` directly from **inside the server via SSH**, the request originates from `127.0.0.1` or the server's public IP. Because those IPs are whitelisted to prevent local Cron jobs and artisan daemons from locking themselves out, the server will legitimately return `200 OK`.  
> To test real bot blocking, run the `curl` probe from an **external workstation or laptop**:
> ```bash
> # Run this from your laptop or external IP:
> curl -s -i -A "curl/8.4.0" "https://yourdomain.com/"
> # Response: HTTP 403 Forbidden ("errors.bot-detected")
> ```
> Or test programmatically via Tinker:
> ```bash
> php artisan tinker --execute="
> \$req = \Illuminate\Http\Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'curl/8.4.0', 'REMOTE_ADDR' => '1.2.3.4']);
> \$res = app(\Jlrodriguezdev\BotProtection\Middleware\BotProtection::class)->handle(\$req, fn(\$r) => 'OK');
> echo 'Status: ' . (is_string(\$res) ? \$res : \$res->getStatusCode()) . PHP_EOL;
> "
> # Output: Status: 403
> ```

---

### 5. Architecture & Decision Flow

```mermaid
flowchart TD
    A[Incoming Request] --> B{Bot Protection Active?}
    B -- No --> Z[Allow Request]
    B -- Yes --> C{SEO Whitelist: Google / Yandex / Social?}
    C -- Yes --> Z
    C -- No --> D{IP in Whitelist?}
    D -- Yes --> Z
    D -- No --> E{IP in Blacklist?}
    E -- Yes --> F[Abort 403: Bot Detected]
    E -- No --> G{Scanner Signature: Curl / Python / Headless?}
    G -- Yes --> F
    G -- No --> H{Invalid HTTP Method on Sensitive Route?}
    H -- Yes --> I[Increment Violation in Cache]
    I --> J{Violations >= 3?}
    J -- Yes --> K[Auto-Blacklist IP Permanently]
    K --> L[Abort 405 / 403]
    J -- No --> L
    H -- No --> M{Exceeds Rate Limit?}
    M -- Yes --> N[Abort 429: Too Many Requests]
    M -- No --> Z
```

---

## 📄 License
MIT License. Created by [jlrodriguezdev](https://github.com/jlrodriguezdev).
