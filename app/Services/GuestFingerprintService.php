<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestFingerprintService
{
    /**
     * Known country names in Spanish.
     */
    protected static array $countryNames = [
        'VE' => 'Venezuela',
        'ES' => 'España',
        'CL' => 'Chile',
        'US' => 'Estados Unidos',
        'MX' => 'México',
        'CO' => 'Colombia',
        'AR' => 'Argentina',
        'PE' => 'Perú',
        'EC' => 'Ecuador',
        'UY' => 'Uruguay',
        'DO' => 'Rep. Dominicana',
        'PA' => 'Panamá',
        'CR' => 'Costa Rica',
        'GT' => 'Guatemala',
        'HN' => 'Honduras',
        'SV' => 'El Salvador',
        'NI' => 'Nicaragua',
        'PY' => 'Paraguay',
        'BO' => 'Bolivia',
        'BR' => 'Brasil',
        'PT' => 'Portugal',
        'FR' => 'Francia',
        'DE' => 'Alemania',
        'IT' => 'Italia',
        'GB' => 'Reino Unido',
        'CA' => 'Canadá',
    ];

    /**
     * Resolve full geolocation, device platform, guest alias, and action correlation.
     */
    public static function resolve(Request $request, ?Response $response = null): array
    {
        $ip = $request->ip() ?? '127.0.0.1';
        $ua = (string) $request->userAgent();

        // 1. Geolocation from Cloudflare header or LAN fallback
        $countryCode = self::detectCountryCode($request);
        $countryFlag = self::countryCodeToFlag($countryCode);
        $countryName = self::countryCodeToName($countryCode);

        // 2. Platform / OS
        $os = self::detectOperatingSystem($ua);

        // 3. Deterministic Guest Correlative
        $correlative = self::generateCorrelative($ip, $ua, $request);
        $alias = "{$countryFlag} Invitado-{$countryCode} · {$os} #{$correlative}";

        // 4. Action Details & Linked Book
        [$actionDetails, $bookId] = self::detectActionAndBook($request, $response);

        return [
            'country_code' => $countryCode,
            'country_name' => $countryName,
            'country_flag' => $countryFlag,
            'os' => $os,
            'correlative' => $correlative,
            'alias' => $alias,
            'action_details' => $actionDetails,
            'book_id' => $bookId,
        ];
    }

    /**
     * Detect 2-letter ISO country code from Cloudflare or fallback.
     */
    public static function detectCountryCode(Request $request): string
    {
        $cfCountry = $request->header('CF-IPCountry')
            ?? $request->server('HTTP_CF_IPCOUNTRY')
            ?? $request->header('X-Country-Code');

        if ($cfCountry && is_string($cfCountry)) {
            $code = strtoupper(trim($cfCountry));
            if (strlen($code) === 2 && ctype_alpha($code) && ! in_array($code, ['XX', 'T1'])) {
                return $code;
            }
        }

        $ip = $request->ip() ?? '127.0.0.1';
        if (self::isLocalOrPrivateIp($ip)) {
            return 'LOC';
        }

        return 'INTL';
    }

    /**
     * Convert 2-letter ISO country code to Unicode Flag Emoji.
     */
    public static function countryCodeToFlag(string $code): string
    {
        $code = strtoupper(trim($code));

        if ($code === 'LOC') {
            return '🏠';
        }

        if ($code === 'INTL' || strlen($code) !== 2 || ! ctype_alpha($code)) {
            return '🌐';
        }

        // Standard Unicode regional indicator symbols: A = 0x1F1E6
        $first = 127397 + ord($code[0]);
        $second = 127397 + ord($code[1]);

        return mb_chr($first, 'UTF-8').mb_chr($second, 'UTF-8');
    }

    /**
     * Return human-friendly country name.
     */
    public static function countryCodeToName(string $code): string
    {
        $code = strtoupper(trim($code));

        if ($code === 'LOC') {
            return 'Homelab Local';
        }

        if (isset(self::$countryNames[$code])) {
            return self::$countryNames[$code];
        }

        return $code === 'INTL' ? 'Internacional' : $code;
    }

    /**
     * Detect OS/Platform from User Agent.
     */
    public static function detectOperatingSystem(string $ua): string
    {
        if (empty($ua)) {
            return 'Web';
        }

        $lower = strtolower($ua);

        if (str_contains($lower, 'android')) {
            return 'Android';
        }

        if (str_contains($lower, 'iphone') || str_contains($lower, 'ipad') || str_contains($lower, 'ipod')) {
            return 'iOS';
        }

        if (str_contains($lower, 'windows') || str_contains($lower, 'win32') || str_contains($lower, 'win64')) {
            return 'Windows';
        }

        if (str_contains($lower, 'macintosh') || str_contains($lower, 'mac os x')) {
            return 'macOS';
        }

        if (str_contains($lower, 'linux')) {
            return 'Linux';
        }

        if (str_contains($lower, 'cros')) {
            return 'ChromeOS';
        }

        return 'Web';
    }

    /**
     * Generate deterministic 3 or 4-digit guest number based on IP + UA.
     */
    public static function generateCorrelative(string $ip, string $ua, Request $request): string
    {
        $sessionId = $request->hasSession() ? $request->session()->getId() : '';
        $seed = $ip.'|'.$ua.'|'.($sessionId ? substr($sessionId, 0, 16) : '');
        $num = (abs(crc32($seed)) % 900) + 100; // Produces 100 to 999

        return (string) $num;
    }

    /**
     * Inspect route, method and parameters to deduce what action was performed and if a book was targeted.
     */
    public static function detectActionAndBook(Request $request, ?Response $response = null): array
    {
        $path = '/'.ltrim($request->path(), '/');
        $method = strtoupper($request->method());
        $action = 'Navegación general';
        $bookId = null;

        // 1. Check if route has {book} parameter
        if (preg_match('#^/books/(\d+)(/.*)?$#', $path, $matches)) {
            $bookId = (int) $matches[1];
            $subpath = $matches[2] ?? '';

            if ($method === 'GET' && empty($subpath)) {
                $book = self::findBookTitle($bookId);
                $title = $book ? " «{$book->title}»" : '';
                $action = "Viendo audiolibro{$title} (#{$bookId})";
            } elseif (str_contains($subpath, '/pdf') || str_contains($subpath, '/stream-doc')) {
                $action = "Leyendo documento original (#{$bookId})";
            } elseif (str_contains($subpath, '/stream-summary')) {
                $action = "Escuchando síntesis ejecutiva (#{$bookId})";
            } elseif (str_contains($subpath, '/status')) {
                $action = "Sondeando estado de síntesis (#{$bookId})";
            } else {
                $action = "Interactuando con libro (#{$bookId})";
            }

            return [$action, $bookId];
        }

        // 2. Chapters stream: /chapters/{id}/stream
        if (preg_match('#^/chapters/(\d+)/stream#', $path, $matches)) {
            $chapterId = (int) $matches[1];
            $action = "Escuchando audio (Capítulo #{$chapterId})";

            return [$action, null];
        }

        // 3. New book creation POST /books
        if ($method === 'POST' && $path === '/books') {
            // Check if response redirected to /books/{id}
            if ($response && $response->isRedirection()) {
                $target = $response->headers->get('Location') ?? '';
                if (preg_match('#/books/(\d+)#', $target, $targetMatches)) {
                    $bookId = (int) $targetMatches[1];
                    $book = self::findBookTitle($bookId);
                    $title = $book ? " «{$book->title}»" : '';
                    $action = "Creó audiolibro{$title} (#{$bookId})";

                    return [$action, $bookId];
                }
            }

            return ['Envió documento para procesar', null];
        }

        // 4. OCR preview POST /books/ocr-preview
        if (str_contains($path, 'ocr-preview')) {
            return ['Escaneó documento con OCR', null];
        }

        // 5. Admin routes
        if (str_starts_with($path, '/admin')) {
            if (str_contains($path, 'telemetry')) {
                return ['Consultó panel de telemetría', null];
            }
            if (str_contains($path, 'users')) {
                return ['Administrando usuarios', null];
            }
            if (str_contains($path, 'tickets')) {
                return ['Revisando tickets de soporte', null];
            }

            return ['Panel de administración', null];
        }

        // 6. Common user paths
        if ($path === '/' || $path === '/books') {
            return ['Explorando catálogo de audiolibros', null];
        }

        if ($path === '/books/create') {
            return ['En pantalla de creación / subida', null];
        }

        if (str_starts_with($path, '/login')) {
            return [$method === 'POST' ? 'Inició sesión en la app' : 'En pantalla de login', null];
        }

        if (str_starts_with($path, '/register')) {
            return [$method === 'POST' ? 'Completó registro con email' : 'En pantalla de registro', null];
        }

        if (str_starts_with($path, '/tickets')) {
            return ['Consultando soporte o cuotas', null];
        }

        return [$action, null];
    }

    /**
     * Safely lookup book title without crashing.
     */
    protected static function findBookTitle(int $bookId): ?Book
    {
        try {
            return Book::find($bookId, ['id', 'title']);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Check if IP is localhost or private subnet.
     */
    protected static function isLocalOrPrivateIp(string $ip): bool
    {
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
