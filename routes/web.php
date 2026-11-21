<?php

use App\Http\Controllers\Admin\TelemetryController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ChunkedUploadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\VoicePreviewController;
use Illuminate\Support\Facades\Route;

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Password Reset Routes
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public / Guest Trial Creation & Reset Routes (Allowed for test conversions, rate-limited against abuse)
Route::match(['get', 'post'], '/guest/reset', [BookController::class, 'resetGuest'])->name('guest.reset');
Route::post('/books/ocr-preview', [BookController::class, 'ocrPreview'])->middleware('throttle:heavy-preview')->name('books.ocr.preview');
Route::post('/books/stt-preview', [BookController::class, 'sttPreview'])->middleware('throttle:heavy-preview')->name('books.stt.preview');
Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
Route::post('/books', [BookController::class, 'store'])->middleware('throttle:heavy-preview')->name('books.store');
Route::get('/voices/preview', [VoicePreviewController::class, 'preview'])->middleware('throttle:heavy-preview')->name('voices.preview');

// Player, Status & PDF Streaming Routes (Permission checked in Controller via BookPolicy)
Route::prefix('books')->name('books.')->group(function () {
    Route::get('/{book}', [BookController::class, 'show'])->name('show');
    Route::get('/{book}/pdf', [BookController::class, 'pdfStream'])->name('pdf');
    Route::get('/{book}/transcription', [BookController::class, 'downloadTranscription'])->name('transcription.download');
    Route::get('/{book}/document-content', [BookController::class, 'documentContent'])->name('document.content');
    Route::get('/{book}/status', [BookController::class, 'status'])->name('status');
    Route::get('/{book}/summary/stream', [BookController::class, 'streamSummary'])->name('summary.stream');
    Route::post('/{book}/retry', [BookController::class, 'retry'])->name('retry');
    Route::delete('/{book}', [BookController::class, 'destroy'])->name('destroy')->middleware('auth');
});

Route::prefix('chapters')->name('chapters.')->group(function () {
    Route::get('/{chapter}/stream', [BookController::class, 'streamChapter'])->name('stream');
    Route::get('/{chapter}/download', [BookController::class, 'downloadChapter'])->name('download');
    Route::post('/{chapter}/retry', [BookController::class, 'retryChapter'])->name('retry');
});

// Root Landing: Cacheable HomeController replacing route closure
Route::get('/', [HomeController::class, 'index'])->name('home');

// Support & Feedback Routes (Tickets & Quota Extensions)
Route::post('/support/tickets', [SupportTicketController::class, 'store'])->middleware('throttle:heavy-preview')->name('tickets.store');
Route::post('/support/request-extension', [SupportTicketController::class, 'requestExtension'])->name('tickets.request-extension')->middleware('auth');

// Authenticated Main Library
Route::middleware('auth')->group(function () {
    Route::post('/uploads/chunk', [ChunkedUploadController::class, 'store'])
        ->middleware(['admin', 'throttle:uploads'])
        ->name('uploads.chunk');
    Route::get('/books', [BookController::class, 'index'])->name('books.index');

    // Profile & Account Settings
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin Control Center: User Management, Telemetry & Support Tickets
    Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::match(['post', 'patch'], '/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::match(['post', 'patch'], '/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.status');
        Route::match(['post', 'patch'], '/users/{user}/adjust-limit', [UserController::class, 'adjustLimit'])->name('users.adjust-limit');
        Route::match(['post', 'patch'], '/users/{user}/limit', [UserController::class, 'adjustLimit'])->name('users.limit');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Telemetry & Metrics
        Route::get('/telemetry', [TelemetryController::class, 'index'])->name('telemetry.index');
        Route::post('/telemetry/clear-logs', [TelemetryController::class, 'clearLogs'])->name('telemetry.clear-logs');

        // Support Tickets & Quota Extensions
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{ticket}/approve-extension', [TicketController::class, 'approveExtension'])->name('tickets.approve-extension');
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    });
});
