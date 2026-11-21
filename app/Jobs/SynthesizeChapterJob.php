<?php

namespace App\Jobs;

use App\Enums\BookStatus;
use App\Enums\ChapterStatus;
use App\Models\Chapter;
use App\Services\AudioSynthesisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SynthesizeChapterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $chapterId;

    public int $timeout = 660;

    public function __construct(int $chapterId)
    {
        $this->chapterId = $chapterId;
    }

    public function handle(AudioSynthesisService $synthesizer): void
    {
        $lock = Cache::lock("synthesize_chapter_{$this->chapterId}", 700);
        if (! $lock->get()) {
            Log::warning("SynthesizeChapterJob: Capítulo ID {$this->chapterId} ya se encuentra en síntesis por otro worker.");

            return;
        }

        try {
            $chapter = Chapter::with('book')->find($this->chapterId);
            if (! $chapter || ! $chapter->book) {
                Log::error("SynthesizeChapterJob: Capítulo ID {$this->chapterId} o su libro no fue encontrado.");

                return;
            }

            $book = $chapter->book;
            if ($book->transcription_only) {
                return;
            }

            $chapter->update([
                'status' => ChapterStatus::Synthesizing->value,
                'error_message' => null,
            ]);

            $relativeAudioPath = "audiobooks/{$book->id}/chapter_{$chapter->chapter_number}.mp3";
            $absoluteAudioPath = Storage::disk('public')->path($relativeAudioPath);

            $ttsResult = $synthesizer->synthesize(
                text: (string) $chapter->content_text,
                outputAbsolutePath: $absoluteAudioPath,
                voice: $book->voice ?: 'es-VE-SebastianNeural',
                rate: $book->speed_rate ?: '+0%',
                pitch: $book->pitch ?: '+0Hz'
            );

            $chapter->update([
                'audio_path' => $relativeAudioPath,
                'duration_seconds' => (int) ($ttsResult['duration_seconds'] ?? 0),
                'status' => ChapterStatus::Ready->value,
                'error_message' => null,
            ]);

            $readyChapters = $book->chapters()->where('status', ChapterStatus::Ready->value);
            $book->update([
                'processed_chapters' => $readyChapters->count(),
                'total_duration' => (int) $readyChapters->sum('duration_seconds'),
                'status' => BookStatus::Ready->value,
                'error_message' => null,
            ]);

            Log::info("SynthesizeChapterJob: Capítulo {$chapter->chapter_number} (ID {$chapter->id}) del libro {$book->id} sintetizado correctamente.");
        } catch (Throwable $e) {
            Log::error("SynthesizeChapterJob: Error sintetizando capítulo ID {$this->chapterId}: ".$e->getMessage());

            Chapter::whereKey($this->chapterId)->update([
                'status' => ChapterStatus::Failed->value,
                'error_message' => $e->getMessage(),
            ]);
        } finally {
            $lock->release();
        }
    }
}
