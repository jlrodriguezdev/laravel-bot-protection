# 🛡️ Laravel BotProtection (Русский)

> **Высокопроизводительный Web Application Firewall (WAF) и Middleware для защиты Laravel от ботов**  
> *Проверен в боевых условиях и работает в продакшене на платформе [LATIRU.RU](https://latiru.ru).*

---

## 🌐 Другие языки
* 🇬🇧 [English Version (Default README.md)](README.md)
* 🇪🇸 [Versión en Español (README.es.md)](README.es.md)

---

### 1. Реальный кейс в продакшене ([LATIRU.RU](https://latiru.ru))
**22 октября 2026 года**, во время глобальной волны сканирований и атак **PHP2Shell (CVE-2026-109618 / CVE-2026-109619)**, наш боевой сервер [LATIRU.RU](https://latiru.ru) подвергся автоматизированному сканированию уязвимостей ботами:

```text
POST /php-cgi/php-cgi.exe?%ADd%20cgi.force_redirect%... -> 405 (Отклонено)
POST /api/gql                                           -> 405 (Отклонено)
POST /graphql/api                                       -> 405 (Отклонено)
GET  /.env                                              -> 404 (Защищено)
GET  /.git/config                                       -> 404 (Защищено)
GET  /.vscode/sftp.json                                 -> 404 (Защищено)
```

#### Как система остановила атаку:
По данным мониторинга **Laravel Telescope**:
1. Бот начал отправлять вредоносные запросы (`POST` на закрытые эндпоинты).
2. Middleware зафиксировал нарушения в кэше (`method_violation:$ip`).
3. При 3-м нарушении **IP-адрес злоумышленника был автоматически заблокирован на 24 часа**.
4. Все последующие попытки открыть публичные страницы (`/`, `/events`, `/calc`) мгновенно сбрасывались с кодом **`403 Forbidden` (`errors.bot-detected`)**, сохранив ресурсы воркеров PHP и базы данных MySQL.

---

### 2. Ключевые возможности
* **Блокировка парсеров и сканеров**: Отсекает запросы от headless-браузеров и утилит (`curl`, `wget`, `python-requests`, `scrapy`, `selenium`, `webdriver`, `phantomjs`).
* **Белый список поисковых систем (SEO Friendly)**: Полный пропуск официальных индексаторов:
  - **Яндекс**: `yandexbot`, `yandeximages`, `yandexvideo`, `yandexsitelinks`, `yandexmarket`, `yandexaccessibility`.
  - **Google**: `googlebot`, `google-inspectiontool`, `google-extended`.
  - **Bing**: `bingbot`, `bingpreview`, `msnbot`.
  - **Превью мессенджеров**: TelegramBot, WhatsApp, TwitterBot, Facebot.
* **Система штрафов (Strike System)**: Автоматический бан при попытке перебора чувствительных точек входа.
* **Адаптивный Rate Limiting**: Ограничение частоты запросов с понятным ответом HTTP 429 (`errors.too-many-requests`).

---

### 3. Установка

```bash
composer require jlrodriguezdev/laravel-bot-protection
```

Подключение в `app/Http/Kernel.php`:
```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Jlrodriguezdev\BotProtection\Middleware\BotProtection::class,
    ],
];
```

Публикация конфигурации и представлений:
```bash
php artisan vendor:publish --provider="Jlrodriguezdev\BotProtection\BotProtectionServiceProvider"
```

---

### 4. Архитектура работы

```mermaid
flowchart TD
    A[Входящий запрос] --> B{Защита активна?}
    B -- Нет --> Z[Пропустить запрос]
    B -- Да --> C{Поисковый робот: Google / Яндекс / Мессенджеры?}
    C -- Да --> Z
    C -- Нет --> D{IP в белом списке?}
    D -- Да --> Z
    D -- Нет --> E{IP в черном списке?}
    E -- Да --> F[Сброс 403: Бот обнаружен]
    E -- Нет --> G{Сигнатура сканера: Curl / Python / Selenium?}
    G -- Да --> F
    G -- Нет --> H{Некорректный HTTP-метод на защищенный URL?}
    H -- Да --> I[Начислить штраф в кэше]
    I --> J{Штрафов >= 3?}
    J -- Да --> K[Бан IP в черный список]
    K --> L[Сброс 405 / 403]
    J -- Нет --> L
    H -- Нет --> M{Превышен Rate Limit?}
    M -- Да --> N[Сброс 429: Too Many Requests]
    M -- Нет --> Z
```

---

## 📄 Лицензия
MIT License. Разработано [jlrodriguezdev](https://github.com/jlrodriguezdev).
