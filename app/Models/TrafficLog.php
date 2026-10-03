<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrafficLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'ip_hash',
        'user_id',
        'session_id',
        'path',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
