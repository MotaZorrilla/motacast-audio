<?php

namespace App\Models;

use App\Enums\ChapterStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Screen\AsSource;

class Chapter extends Model
{
    use AsSource;

    protected $fillable = [
        'book_id',
        'chapter_number',
        'title',
        'content_text',
        'audio_path',
        'duration_seconds',
        'word_count',
        'status',
        'error_message',
    ];

    protected $casts = [
        'chapter_number' => 'integer',
        'duration_seconds' => 'integer',
        'word_count' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function statusEnum(): ChapterStatus
    {
        return ChapterStatus::tryFrom($this->status) ?? ChapterStatus::Pending;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = $value instanceof ChapterStatus ? $value->value : $value;
    }

    public function isReady(): bool
    {
        return $this->status === ChapterStatus::Ready->value;
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getAudioStreamUrlAttribute(): ?string
    {
        if (! $this->audio_path) {
            return null;
        }

        return route('chapters.stream', $this->id);
    }

    public function getAudioDownloadUrlAttribute(): ?string
    {
        if (! $this->audio_path) {
            return null;
        }

        return route('chapters.download', $this->id);
    }
}
