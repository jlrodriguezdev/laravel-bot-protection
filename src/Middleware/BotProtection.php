<?php

namespace Jlrodriguezdev\BotProtection\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BotProtection
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Verify if Bot Protection is globally enabled
        if (!config('bot-protection.enabled', true)) {
            return $next($request);
        }

        $ip = $request->ip();
        $userAgent = strtolower($request->header('User-Agent', ''));

        // 2. SEO & Legitimate Crawlers Whitelist (Google, Yandex, Bing, Telegram, WhatsApp)
        $whitelistedAgents = config('bot-protection.whitelist_agents', []);
        foreach ($whitelistedAgents as $whitelistedAgent) {
            if (stripos($userAgent, $whitelistedAgent) !== false) {
                return $next($request);
            }
        }

        // 3. Trusted IPs Whitelist
        $whitelistedIps = config('bot-protection.whitelist_ips', []);
        if (in_array($ip, $whitelistedIps, true)) {
            return $next($request);
        }

        // 4. Blacklisted IPs (Immediate 403 response)
        $cachedBlacklist = Cache::get('bot_blacklisted_ips_cache', []);
        $configuredBlacklist = config('bot-protection.blacklisted_ips', []);
        $blacklistedIps = array_unique(array_merge($configuredBlacklist, $cachedBlacklist));

        if (in_array($ip, $blacklistedIps, true)) {
            $this->logEvent($request, 'blocked', 'IP address is permanently blacklisted.');
            return $this->respondBlocked('Su dirección IP ha sido bloqueada por actividades sospechosas.');
        }

        // 5. Automated Scraper / Headless Signature Detection
        $botPatterns = config('bot-protection.bot_patterns', []);
        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                $this->logEvent($request, 'blocked', "Automated scraper pattern detected: {$pattern}");
                return $this->respondBlocked('Acceso no permitido para herramientas automatizadas.');
            }
        }

        // 6. Strike-based Invalid HTTP Method Protection on Sensitive Routes
        $sensitiveRoutes = config('bot-protection.sensitive_routes', [
            '/' => ['GET', 'HEAD'],
            '/index.php' => ['GET', 'HEAD'],
            '/api' => ['GET', 'HEAD'],
            '/admin' => ['GET', 'HEAD'],
        ]);

        $path = '/' . ltrim($request->path(), '/');
        if (isset($sensitiveRoutes[$path]) && !in_array($request->method(), $sensitiveRoutes[$path], true)) {
            $this->logEvent($request, 'violation', "Invalid HTTP method {$request->method()} for sensitive route {$path}");

            // Increment strike counter in Cache for 24 hours
            $violationKey = 'bot_method_violations:' . $ip;
            $violations = (int) Cache::get($violationKey, 0) + 1;
            Cache::put($violationKey, $violations, now()->addHours(24));

            $threshold = (int) config('bot-protection.strike_threshold', 3);
            if ($violations >= $threshold) {
                $cachedBlacklist[] = $ip;
                Cache::forever('bot_blacklisted_ips_cache', array_unique($cachedBlacklist));
                Log::warning("🚨 [BotProtection] IP {$ip} auto-blacklisted due to {$violations} violations.");
            }

            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        // 7. Contextual Rate Limiting
        $maxAttempts = (int) config('bot-protection.rate_limit', 60);
        $decayMinutes = (int) config('bot-protection.rate_limit_decay_minutes', 1);
        $rateLimitKey = 'bot_rate_limit:' . $ip;

        $attempts = (int) Cache::get($rateLimitKey, 0);
        if ($attempts >= $maxAttempts) {
            $this->logEvent($request, 'rate_limited', "Exceeded {$maxAttempts} requests per minute");
            return $this->respondTooManyRequests();
        }

        Cache::put($rateLimitKey, $attempts + 1, now()->addMinutes($decayMinutes));

        // 8. Browser Challenge (Under Attack Mode)
        $challengeMode = config('bot-protection.challenge_mode', 'medium');
        if (in_array($challengeMode, ['hard', 'under_attack'], true) && !$request->hasCookie('verified_browser')) {
            if (!$request->expectsJson() && !$request->ajax()) {
                $this->logEvent($request, 'challenged', 'Browser challenge verification required');
                return $this->respondChallenge($request->fullUrl());
            }
        }

        return $next($request);
    }

    /**
     * Render 403 Forbidden response.
     */
    protected function respondBlocked(string $reason)
    {
        if (view()->exists('bot-protection::errors.bot-detected')) {
            return response()->view('bot-protection::errors.bot-detected', ['reason' => $reason], 403);
        }

        return response()->json([
            'error' => 'Forbidden',
            'message' => $reason,
        ], 403);
    }

    /**
     * Render 429 Too Many Requests response.
     */
    protected function respondTooManyRequests()
    {
        if (view()->exists('bot-protection::errors.too-many-requests')) {
            return response()->view('bot-protection::errors.too-many-requests', [], 429);
        }

        return response()->json([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Please try again shortly.',
        ], 429);
    }

    /**
     * Render browser verification challenge.
     */
    protected function respondChallenge(string $redirectUrl)
    {
        if (view()->exists('bot-protection::security.verify-browser')) {
            return response()->view('bot-protection::security.verify-browser', [
                'redirectTo' => $redirectUrl,
            ]);
        }

        return response('Verifying browser...', 200);
    }

    /**
     * Log security events gracefully.
     */
    protected function logEvent(Request $request, string $action, string $reason): void
    {
        Log::channel(config('logging.default', 'stack'))->info("[BotProtection] Action: {$action} | IP: {$request->ip()} | Reason: {$reason}");
    }
}
