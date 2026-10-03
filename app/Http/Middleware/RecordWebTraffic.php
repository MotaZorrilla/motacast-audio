<?php

namespace App\Http\Middleware;

use App\Models\TrafficLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RecordWebTraffic
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     * Guarantees 0ms perceived latency for the visitor.
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($this->shouldIgnore($request)) {
            return;
        }

        try {
            $userAgent = (string) $request->userAgent();
            $path = '/' . ltrim($request->path(), '/');
            $ip = $request->ip() ?? '127.0.0.1';
            $ipHash = hash('sha256', $ip . (config('app.key') ?? 'motacast_salt'));

            $isCrawler = $this->detectCrawler($userAgent);
            $deviceType = $isCrawler ? 'bot' : $this->detectDevice($userAgent);

            $referer = $request->headers->get('referer');
            if ($referer) {
                $referer = mb_substr($referer, 0, 500);
            }

            $userId = Auth::id() ?? $request->user()?->id;
            $sessionId = null;
            if ($request->hasSession()) {
                $sessionId = $request->session()->getId();
            }

            TrafficLog::create([
                'ip_hash' => $ipHash,
                'user_id' => $userId,
                'session_id' => $sessionId ? mb_substr($sessionId, 0, 64) : null,
                'path' => mb_substr($path, 0, 255),
                'method' => mb_substr($request->method(), 0, 10),
                'status_code' => $response->getStatusCode(),
                'referer' => $referer,
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 1000) : null,
                'device_type' => $deviceType,
                'is_crawler' => $isCrawler,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail silently to never affect the client response
        }
    }

    /**
     * Determine if request should be ignored from telemetry logs.
     */
    protected function shouldIgnore(Request $request): bool
    {
        $path = $request->path();

        // Ignore internal healthcheck
        if ($path === 'up') {
            return true;
        }

        // Ignore static assets and Vite build bundles
        if (preg_match('/\.(css|js|map|jpg|jpeg|png|gif|svg|ico|webp|woff|woff2|ttf|eot)$/i', $path)) {
            return true;
        }

        if (str_starts_with($path, 'build/') || str_starts_with($path, 'images/') || str_starts_with($path, 'favicon')) {
            return true;
        }

        // Avoid flooding table with repetitive byte-range audio chunk requests
        if (str_contains($path, '/stream') && $request->headers->has('Range')) {
            return true;
        }

        return false;
    }

    /**
     * Detect if User-Agent belongs to a social media preview crawler or search engine bot.
     */
    protected function detectCrawler(string $ua): bool
    {
        if (empty($ua)) {
            return false;
        }

        $crawlers = [
            'whatsapp',
            'facebookexternalhit',
            'twitterbot',
            'telegrambot',
            'linkedinbot',
            'slackbot',
            'discordbot',
            'googlebot',
            'bingbot',
            'yandex',
            'baiduspider',
            'duckduckbot',
            'applebot',
            'meta-externalagent',
        ];

        $lower = strtolower($ua);
        foreach ($crawlers as $crawler) {
            if (str_contains($lower, $crawler)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Categorize device type into mobile, tablet, or desktop.
     */
    protected function detectDevice(string $ua): bool
    {
        if (empty($ua)) {
            return 'desktop';
        }

        $lower = strtolower($ua);

        if (str_contains($lower, 'ipad') || str_contains($lower, 'tablet')) {
            return 'tablet';
        }

        if (
            str_contains($lower, 'mobile') ||
            str_contains($lower, 'android') ||
            str_contains($lower, 'iphone') ||
            str_contains($lower, 'ipod') ||
            str_contains($lower, 'blackberry') ||
            str_contains($lower, 'webos') ||
            str_contains($lower, 'opera mini')
        ) {
            return 'mobile';
        }

        return 'desktop';
    }
}
