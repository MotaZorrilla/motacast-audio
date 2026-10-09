<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GuestSessionService
{
    public const SESSION_COUNT_KEY = 'guest_upload_count';

    public const SESSION_BOOK_KEY = 'guest_book_id';

    /**
     * Check if current guest has exhausted their free trial conversion.
     */
    public function isTrialExhausted(): bool
    {
        if (Auth::check()) {
            return false;
        }

        return (int) session(self::SESSION_COUNT_KEY, 0) >= 1;
    }

    /**
     * Reset guest trial session state completely.
     */
    public function resetSession(): void
    {
        session()->forget([self::SESSION_BOOK_KEY, self::SESSION_COUNT_KEY]);
        session()->save();
    }

    /**
     * Record a new trial upload for guest.
     */
    public function recordTrialUpload(int $bookId): void
    {
        if (! Auth::check()) {
            $current = (int) session(self::SESSION_COUNT_KEY, 0);
            session([
                self::SESSION_COUNT_KEY => $current + 1,
                self::SESSION_BOOK_KEY => $bookId,
            ]);
            session()->save();
        }
    }

    /**
     * Check if guest owns the given book in their current session.
     */
    public function guestOwnsBook(Book $book): bool
    {
        if (Auth::check()) {
            return false;
        }

        return (int) session(self::SESSION_BOOK_KEY) === (int) $book->id;
    }

    /**
     * Retrieve the current guest trial book if present and valid.
     */
    public function getActiveTrialBook(): ?Book
    {
        if (! session()->has(self::SESSION_BOOK_KEY)) {
            return null;
        }

        $book = Book::find(session(self::SESSION_BOOK_KEY));
        if (! $book) {
            $this->resetSession();

            return null;
        }

        return $book;
    }

    /**
     * Claim and transfer the active trial book to a newly registered or authenticated user.
     */
    public function claimTrialBook(User $user): ?Book
    {
        $book = $this->getActiveTrialBook();
        if (! $book) {
            return null;
        }

        // Only adopt if the book was unassigned (user_id IS NULL)
        if ($book->user_id === null) {
            $book->user_id = $user->id;
            $book->save();
        }

        $this->resetSession();

        return $book;
    }
}
