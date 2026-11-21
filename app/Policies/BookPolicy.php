<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use App\Services\GuestSessionService;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookPolicy
{
    use HandlesAuthorization;

    public function __construct(
        protected GuestSessionService $guestSession
    ) {}

    /**
     * Determina si el usuario o visitante tiene autorización para ver el libro.
     * Los libros privados nunca se exponen por User-Agent spoofeable.
     */
    public function view(?User $user, Book $book): bool
    {
        // 1. Libros públicos/demo (sin propietario) o audiolibro perteneciente a la sesión de invitado activa
        if (is_null($book->user_id) || $this->guestSession->guestOwnsBook($book)) {
            return true;
        }

        // 2. Libros privados registrados: requieren autenticación y rol de propietario o administrador
        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $book->user_id === $user->id;
    }

    /**
     * Determina si el usuario tiene autorización para reintentar el procesamiento.
     */
    public function retry(?User $user, Book $book): bool
    {
        return $this->view($user, $book);
    }

    /**
     * Determina si el usuario tiene autorización para reproducir o descargar audios del libro.
     */
    public function stream(?User $user, Book $book): bool
    {
        return $this->view($user, $book);
    }

    /**
     * Determina si el usuario tiene autorización para eliminar el libro y sus activos.
     */
    public function delete(?User $user, Book $book): bool
    {
        // Invitados pueden eliminar sus libros de sesión propios
        if (is_null($book->user_id) && $this->guestSession->guestOwnsBook($book)) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $book->user_id === $user->id;
    }
}
