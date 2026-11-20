<?php

namespace App\Http\Controllers;

use App\Services\ChunkedUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChunkedUploadController extends Controller
{
    public function __construct(
        protected ChunkedUploadService $chunkedUploadService
    ) {}

    /**
     * Receive and assemble chunked file uploads for admin accounts (bypasses single-request proxy caps).
     */
    public function store(Request $request): JsonResponse
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el administrador tiene permitido subir archivos superiores a 100 MB.',
            ], 403);
        }

        $validated = $request->validate([
            'upload_id' => 'required|uuid',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1|max:2000',
            'filename' => 'required|string|max:255',
            'chunk' => 'required|file|max:25600',
        ]);

        try {
            $result = $this->chunkedUploadService->storeChunk(
                uploadId: (string) $validated['upload_id'],
                chunkIndex: (int) $validated['chunk_index'],
                totalChunks: (int) $validated['total_chunks'],
                originalFilename: (string) $validated['filename'],
                chunkFile: $request->file('chunk')
            );

            return response()->json(array_merge(['success' => true], $result));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
