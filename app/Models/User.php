<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'book_limit',
        'beta_disclaimer_accepted_at',
        'auto_extension_used',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'beta_disclaimer_accepted_at' => 'datetime',
            'auto_extension_used' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Default model attributes.
     */
    protected $attributes = [
        'role' => 'user',
        'status' => 'active',
        'book_limit' => 3,
        'auto_extension_used' => false,
    ];

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function canUploadBook(): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        $limit = $this->book_limit ?? 3;

        if ($this->isAdmin() || $limit === -1) {
            return true;
        }

        return $this->books()->count() < $limit;
    }

    public function remainingBooks(): int|string
    {
        $limit = $this->book_limit ?? 3;

        if ($this->isAdmin() || $limit === -1) {
            return 'Ilimitado';
        }

        $current = $this->books()->count();
        return max(0, $limit - $current);
    }

    public function canRequestAutoExtension(): bool
    {
        return !$this->auto_extension_used && !$this->isAdmin();
    }

    public function grantCourtesyExtension(int $additionalBooks = 1): void
    {
        $currentLimit = $this->book_limit ?? 3;
        if ($currentLimit !== -1) {
            $this->book_limit = $currentLimit + $additionalBooks;
        }
        $this->auto_extension_used = true;
        $this->save();
    }
}
