<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public / Guest Trial Creation Routes (Allowed for 1 test conversion before requiring account)
Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
Route::post('/books', [BookController::class, 'store'])->name('books.store');

// Player, Status & PDF Streaming Routes (Permission checked in Controller for User and Guest Trial)
Route::prefix('books')->name('books.')->group(function () {
    Route::get('/{book}', [BookController::class, 'show'])->name('show');
    Route::get('/{book}/pdf', [BookController::class, 'pdfStream'])->name('pdf');
    Route::get('/{book}/document-content', [BookController::class, 'documentContent'])->name('document.content');
    Route::get('/{book}/status', [BookController::class, 'status'])->name('status');
    Route::get('/{book}/summary/stream', [BookController::class, 'streamSummary'])->name('summary.stream');
    Route::post('/{book}/retry', [BookController::class, 'retry'])->name('retry');
    Route::delete('/{book}', [BookController::class, 'destroy'])->name('destroy')->middleware('auth');
});

Route::prefix('chapters')->name('chapters.')->group(function () {
    Route::get('/{chapter}/stream', [BookController::class, 'streamChapter'])->name('stream');
    Route::get('/{chapter}/download', [BookController::class, 'downloadChapter'])->name('download');
});

// Root Landing: First-time guests land on conversion trial, guests with loaded book view their book, logged-in users access library
Route::get('/', function (\Illuminate\Http\Request $request) {
    if (Auth::check()) {
        return app(BookController::class)->index($request);
    }

    // Optional reset switch for guests (?reset=1 or ?new=1)
    if ($request->has('reset') || $request->has('new')) {
        session()->forget(['guest_book_id', 'guest_upload_count']);
        session()->save();
        return app(BookController::class)->create();
    }

    if (session()->has('guest_book_id')) {
        $guestBook = \App\Models\Book::find(session('guest_book_id'));
        if ($guestBook) {
            return redirect()->route('books.show', $guestBook->id);
        }
        // Stale guest book was deleted: clear session so guest can use trial again
        session()->forget(['guest_book_id', 'guest_upload_count']);
    }

    if (session('guest_upload_count', 0) >= 1) {
        return redirect()->route('login')
            ->with('info', 'Has utilizado tu conversión de prueba gratuita. Inicia sesión o regístrate para acceder a tu biblioteca.');
    }
    return app(BookController::class)->create();
})->name('home');

// Authenticated Main Library
Route::middleware('auth')->group(function () {
    Route::get('/books', [BookController::class, 'index'])->name('books.index');

    // Admin Control Center: User Management & Quotas
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::match(['post', 'patch'], '/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::match(['post', 'patch'], '/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.status');
        Route::match(['post', 'patch'], '/users/{user}/adjust-limit', [UserController::class, 'adjustLimit'])->name('users.adjust-limit');
        Route::match(['post', 'patch'], '/users/{user}/limit', [UserController::class, 'adjustLimit'])->name('users.limit');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
