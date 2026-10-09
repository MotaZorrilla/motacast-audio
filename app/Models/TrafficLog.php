<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Screen\AsSource;

class TrafficLog extends Model
{
    use AsSource, Filterable, HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'ip_hash',
        'user_id',
        'country_code',
        'country_name',
        'country_flag',
        'session_id',
        'guest_fingerprint',
        'book_id',
        'path',
        'action_details',
        'method',
        'status_code',
        'referer',
        'user_agent',
        'device_type',
        'is_crawler',
        'created_at',
    ];

    protected $casts = [
        'is_crawler' => 'boolean',
        'status_code' => 'integer',
        'created_at' => 'datetime',
    ];

    protected $allowedFilters = [
        'id' => Where::class,
        'country_code' => Where::class,
        'path' => Like::class,
        'device_type' => Where::class,
        'created_at' => WhereDateStartEnd::class,
    ];

    protected $allowedSorts = [
        'id',
        'country_code',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function scopeRealUsers($query)
    {
        return $query->where('is_crawler', false);
    }

    public function scopeCrawlers($query)
    {
        return $query->where('is_crawler', true);
    }

    public function scopeGuests($query)
    {
        return $query->whereNull('user_id')->where('is_crawler', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
