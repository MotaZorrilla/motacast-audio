<?php

namespace App\Models;

use App\Enums\BookStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'user_id',
        'guest_fingerprint',
        'country_code',
        'title',
        'author',
        'description',
        'summary',
        'summary_audio_path',
        'original_filename',
        'pdf_path',
        'voice',
        'speed_rate',
        'pitch',
        'keep_original_media',
        'transcription_only',
        'status',
        'total_chapters',
        'processed_chapters',
        'total_duration',
        'total_words',
        'error_message',
        'expires_at',
    ];

    protected $casts = [
        'keep_original_media' => 'boolean',
        'transcription_only' => 'boolean',
        'total_chapters' => 'integer',
        'processed_chapters' => 'integer',
        'total_duration' => 'integer',
        'total_words' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function expiresSoon(): bool
    {
        return $this->expires_at !== null
            && ! $this->isExpired()
            && now()->diffInHours($this->expires_at, false) <= 24;
    }

    public function retentionLabel(): ?string
    {
        if ($this->expires_at === null) {
            return null;
        }

        if ($this->isExpired()) {
            return 'Expirado';
        }

        $hoursRemaining = max(0, (int) now()->diffInHours($this->expires_at, false));
        if ($hoursRemaining <= 24) {
            return 'Expira hoy';
        }

        $daysRemaining = (int) ceil($hoursRemaining / 24);

        return "Expira en {$daysRemaining} ".($daysRemaining === 1 ? 'día' : 'días');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trafficLogs(): HasMany
    {
        return $this->hasMany(TrafficLog::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('chapter_number');
    }

    public function statusEnum(): BookStatus
    {
        return BookStatus::tryFrom($this->status) ?? BookStatus::Pending;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = $value instanceof BookStatus ? $value->value : $value;
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->status === BookStatus::Ready->value) {
            return 100;
        }

        if ($this->hasFailed()) {
            return 0;
        }

        if ($this->total_chapters === 0) {
            return $this->status === BookStatus::Extracting->value ? 15 : 5;
        }

        return min(100, (int) round(($this->processed_chapters / $this->total_chapters) * 100));
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->total_duration / 3600);
        $minutes = floor(($this->total_duration % 3600) / 60);
        $seconds = $this->total_duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function isReady(): bool
    {
        return $this->status === BookStatus::Ready->value;
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, [
            BookStatus::Pending->value,
            BookStatus::Extracting->value,
            BookStatus::Synthesizing->value,
        ]);
    }

    public function hasFailed(): bool
    {
        return $this->status === BookStatus::Failed->value;
    }
}
