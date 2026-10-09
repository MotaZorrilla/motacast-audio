<?php

namespace Tests\Unit;

use App\Actions\Book\DeleteBookAction;
use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteBookActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_book_action_removes_model_and_storage_assets(): void
    {
        Storage::fake('public');

        $pdfPath = 'pdfs/test_doc.pdf';

        $book = Book::create([
            'title' => 'Libro para Borrado Limpio',
            'original_filename' => 'test_doc.pdf',
            'pdf_path' => $pdfPath,
            'status' => 'ready',
        ]);

        $audioFile = "audiobooks/{$book->id}/chapter_1.mp3";

        Storage::disk('public')->put($pdfPath, 'dummy pdf content');
        Storage::disk('public')->put($audioFile, 'dummy audio content');

        Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Capítulo 1',
            'content_text' => 'Texto del capítulo',
            'audio_path' => $audioFile,
            'status' => 'ready',
        ]);

        $bookId = $book->id;

        $action = new DeleteBookAction;
        $action->execute($book);

        // Verify model and cascade deletion in database
        $this->assertDatabaseMissing('books', ['id' => $bookId]);
        $this->assertDatabaseMissing('chapters', ['book_id' => $bookId]);

        // Verify physical storage cleanup
        Storage::disk('public')->assertMissing($pdfPath);
        Storage::disk('public')->assertMissing($audioFile);
    }
}
