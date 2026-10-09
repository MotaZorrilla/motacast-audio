<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;

class User extends Authenticatable
{
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
        'permissions',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'email_verified_at' => 'datetime',
        'beta_disclaimer_accepted_at' => 'datetime',
        'auto_extension_used' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Default model attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'user',
        'status' => 'active',
        'book_limit' => 3,
        'auto_extension_used' => false,
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id' => Where::class,
        'name' => Like::class,
        'email' => Like::class,
        'role' => Where::class,
        'status' => Where::class,
        'updated_at' => WhereDateStartEnd::class,
        'created_at' => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'role',
        'status',
        'updated_at',
        'created_at',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function isAdmin(): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        if (! empty($this->permissions) && is_array($this->permissions) && ! empty($this->permissions['platform.index'])) {
            return true;
        }

        return false;
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
        return ! $this->auto_extension_used && ! $this->isAdmin();
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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
