<?php

namespace App\Models;

use App\Enums\BookStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Screen\AsSource;

class Book extends Model
{
    use AsSource, Filterable;

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
        'status',
        'total_chapters',
        'processed_chapters',
        'total_duration',
        'total_words',
        'error_message',
    ];

    protected $casts = [
        'keep_original_media' => 'boolean',
        'total_chapters' => 'integer',
        'processed_chapters' => 'integer',
        'total_duration' => 'integer',
        'total_words' => 'integer',
    ];

    protected $allowedFilters = [
        'id' => Where::class,
        'title' => Like::class,
        'author' => Like::class,
        'status' => Where::class,
        'voice' => Like::class,
        'user_id' => Where::class,
        'created_at' => WhereDateStartEnd::class,
    ];

    protected $allowedSorts = [
        'id',
        'title',
        'author',
        'status',
        'total_duration',
        'created_at',
    ];

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
