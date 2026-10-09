<?php

namespace App\Services;

use App\Http\Requests\StoreBookRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentIngestService
{
    /**
     * Ingest an uploaded file or raw text and return metadata for Book model creation.
     *
     * @return array{title: string, author: ?string, original_filename: string, stored_path: string}
     */
    public function ingest(StoreBookRequest $request): array
    {
        $author = $request->input('author');

        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $originalFilename = $file->getClientOriginalName();
            $title = $request->filled('title')
                ? (string) $request->input('title')
                : pathinfo($originalFilename, PATHINFO_FILENAME);

            $storedPath = $file->store('pdfs', 'public');
        } else {
            $rawText = trim((string) $request->input('raw_text'));
            if ($request->filled('title')) {
                $title = (string) $request->input('title');
            } else {
                $firstLine = strtok($rawText, "\r\n") ?: '';
                $cleanFirstLine = trim(preg_replace('/^[#\s*_-]+/', '', $firstLine));
                $title = Str::limit($cleanFirstLine, 50, '...');
                if (empty($title)) {
                    $title = 'Texto Directo '.now()->format('d/m/Y H:i');
                }
            }

            $slug = Str::slug($title) ?: 'texto-directo';
            $fileName = $slug.'-'.time().'.txt';
            $storedPath = 'pdfs/'.$fileName;

            Storage::disk('public')->put($storedPath, $rawText);
            $originalFilename = $title.'.txt';
        }

        return [
            'title' => $title,
            'author' => $author,
            'original_filename' => $originalFilename,
            'stored_path' => $storedPath,
        ];
    }
}
