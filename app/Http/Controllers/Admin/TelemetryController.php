<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\SupportTicket;
use App\Models\TrafficLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TelemetryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        // 1. Storage & Disk Metrics
        $publicDisk = Storage::disk('public');
        $audiobooksDir = storage_path('app/public/audiobooks');
        $pdfsDir = storage_path('app/public/pdfs');

        $audiobooksBytes = $this->calculateDirectorySize($audiobooksDir);
        $pdfsBytes = $this->calculateDirectorySize($pdfsDir);
        $totalStorageBytes = $audiobooksBytes + $pdfsBytes;

        $freeDiskBytes = @disk_free_space(base_path()) ?: 0;
        $totalDiskBytes = @disk_total_space(base_path()) ?: 0;
        $usedDiskPercentage = $totalDiskBytes > 0 ? round((($totalDiskBytes - $freeDiskBytes) / $totalDiskBytes) * 100, 1) : 0;

        $storageMetrics = [
            'audiobooks_size' => $this->formatBytes($audiobooksBytes),
            'pdfs_size' => $this->formatBytes($pdfsBytes),
            'total_app_storage' => $this->formatBytes($totalStorageBytes),
            'free_disk' => $this->formatBytes($freeDiskBytes),
            'total_disk' => $this->formatBytes($totalDiskBytes),
            'disk_usage_percent' => $usedDiskPercentage,
            'audio_files_count' => $this->countFilesInDir($audiobooksDir, ['mp3', 'wav']),
            'pdf_files_count' => $this->countFilesInDir($pdfsDir, ['pdf', 'docx', 'txt', 'png', 'jpg']),
        ];

        // 2. Conversion & Audio Synthesis Metrics
        $totalBooks = Book::count();
        $readyBooks = Book::where('status', 'ready')->count();
        $failedBooks = Book::where('status', 'failed')->count();
        $processingBooks = Book::whereIn('status', ['pending', 'extracting', 'synthesizing'])->count();
        $successRate = $totalBooks > 0 ? round(($readyBooks / $totalBooks) * 100, 1) : 100;

        $totalChapters = Chapter::count();
        $totalWords = Book::sum('total_words') ?: 0;
        $totalSeconds = Book::sum('total_duration') ?: 0;
        $totalHours = round($totalSeconds / 3600, 1);
        $estimatedCharacters = $totalWords * 5.5; // Average chars per word

        $conversionMetrics = [
            'total_books' => $totalBooks,
            'ready_books' => $readyBooks,
            'failed_books' => $failedBooks,
            'processing_books' => $processingBooks,
            'success_rate' => $successRate,
            'total_chapters' => $totalChapters,
            'total_words' => number_format($totalWords),
            'total_hours' => $totalHours,
            'estimated_characters' => number_format($estimatedCharacters),
        ];

        // 3. User & Quota Metrics
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $suspendedUsers = User::where('status', 'suspended')->count();
        $autoExtensionsUsed = User::where('auto_extension_used', true)->count();

        // Count users who have reached or exceeded their limit
        $usersAtLimit = User::where('role', '!=', 'admin')
            ->where('book_limit', '!=', -1)
            ->withCount('books')
            ->get()
            ->filter(fn ($u) => $u->books_count >= $u->book_limit)
            ->count();

        $userMetrics = [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'suspended_users' => $suspendedUsers,
            'users_at_limit' => $usersAtLimit,
            'auto_extensions_used' => $autoExtensionsUsed,
        ];

        // 4. Tickets & Support Overview
        $ticketMetrics = [
            'total_tickets' => SupportTicket::count(),
            'pending_tickets' => SupportTicket::where('status', 'pendiente')->count(),
            'pending_extensions' => SupportTicket::where('type', 'extension_limite')->where('status', 'pendiente')->count(),
            'resolved_tickets' => SupportTicket::where('status', 'resuelto')->count(),
        ];

        // 5. Traffic, Visitors & Connections Telemetry
        $totalHits = TrafficLog::count();
        $todayHits = TrafficLog::whereDate('created_at', today())->count();
        $uniqueVisitorsToday = TrafficLog::whereDate('created_at', today())->distinct('ip_hash')->count('ip_hash');
        $uniqueVisitorsTotal = TrafficLog::distinct('ip_hash')->count('ip_hash');
        $guestHits = TrafficLog::whereNull('user_id')->where('is_crawler', false)->count();
        $authHits = TrafficLog::whereNotNull('user_id')->count();
        $crawlerHits = TrafficLog::where('is_crawler', true)->count();
        $mobileHits = TrafficLog::where('device_type', 'mobile')->count();
        $desktopHits = TrafficLog::where('device_type', 'desktop')->count();
        $mobilePercentage = ($mobileHits + $desktopHits) > 0 ? round(($mobileHits / ($mobileHits + $desktopHits)) * 100, 1) : 0;

        $topPaths = TrafficLog::selectRaw('path, count(*) as hits')
            ->where('is_crawler', false)
            ->groupBy('path')
            ->orderByDesc('hits')
            ->limit(6)
            ->get();

        $topCountries = TrafficLog::selectRaw('country_code, country_name, country_flag, count(*) as hits')
            ->whereNotNull('country_code')
            ->where('is_crawler', false)
            ->groupBy('country_code', 'country_name', 'country_flag')
            ->orderByDesc('hits')
            ->limit(8)
            ->get();

        $recentVisits = TrafficLog::with(['user:id,name,email', 'book:id,title,status'])
            ->latest('id')
            ->limit(200)
            ->get();

        $trafficMetrics = [
            'total_hits' => $totalHits,
            'today_hits' => $todayHits,
            'unique_today' => $uniqueVisitorsToday,
            'unique_total' => $uniqueVisitorsTotal,
            'guest_hits' => $guestHits,
            'auth_hits' => $authHits,
            'crawler_hits' => $crawlerHits,
            'mobile_hits' => $mobileHits,
            'desktop_hits' => $desktopHits,
            'mobile_percent' => $mobilePercentage,
            'top_paths' => $topPaths,
            'top_countries' => $topCountries,
            'recent_visits' => $recentVisits,
        ];

        // 6. Live Log Tail (Last 70 lines)
        $logs = $this->getRecentLogs(70);

        return view('admin.telemetry', compact(
            'storageMetrics',
            'conversionMetrics',
            'userMetrics',
            'ticketMetrics',
            'trafficMetrics',
            'logs'
        ));
    }

    public function clearLogs(Request $request)
    {
        $this->authorizeAdmin();

        $logFile = storage_path('logs/laravel.log');
        if (File::exists($logFile)) {
            File::put($logFile, '');
        }

        return redirect()->route('admin.telemetry.index')
            ->with('success', 'Registro de logs del servidor reiniciado correctamente.');
    }

    protected function authorizeAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Acceso denegado: Se requieren privilegios de Administrador.');
        }
    }

    private function calculateDirectorySize(string $path): int
    {
        if (! File::isDirectory($path)) {
            return 0;
        }

        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    private function countFilesInDir(string $path, array $extensions): int
    {
        if (! File::isDirectory($path)) {
            return 0;
        }

        $count = 0;
        foreach (File::allFiles($path) as $file) {
            if (in_array(strtolower($file->getExtension()), $extensions)) {
                $count++;
            }
        }

        return $count;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 MB';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    private function getRecentLogs(int $lines = 70): array
    {
        $logFile = storage_path('logs/laravel.log');
        if (! File::exists($logFile)) {
            return ['No hay archivo de registro laravel.log generado aún.'];
        }

        $content = File::get($logFile);
        $allLines = explode("\n", trim($content));
        $slice = array_slice($allLines, -$lines);

        // Sanitize sensitive tokens or passwords if any
        return array_map(function ($line) {
            return preg_replace('/(password|token|secret|key)=[^&\s]+/i', '$1=***REDACTED***', $line);
        }, $slice);
    }
}
