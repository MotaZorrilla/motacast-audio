<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'user_id',
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
        'status',
        'total_chapters',
        'processed_chapters',
        'total_duration',
        'total_words',
        'error_message',
    ];

    protected $casts = [
        'total_chapters' => 'integer',
        'processed_chapters' => 'integer',
        'total_duration' => 'integer',
        'total_words' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('chapter_number');
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->status === 'ready') {
            return 100;
        }

        if ($this->hasFailed()) {
            return 0;
        }

        if ($this->total_chapters === 0) {
            return $this->status === 'extracting' ? 15 : 5;
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
        return $this->status === 'ready';
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, ['pending', 'extracting', 'synthesizing']);
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }
}
