<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
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

    public function book()
    {
        return $this->belongsTo(Book::class);
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
