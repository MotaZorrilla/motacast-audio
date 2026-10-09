<?php

namespace Tests\Unit;

use App\Services\MimeTypeResolver;
use PHPUnit\Framework\TestCase;

class MimeTypeResolverTest extends TestCase
{
    public function test_resolves_standard_document_formats(): void
    {
        $this->assertEquals('application/pdf', MimeTypeResolver::resolve('documento.pdf'));
        $this->assertEquals('application/vnd.openxmlformats-officedocument.wordprocessingml.document', MimeTypeResolver::resolve('tesis.docx'));
        $this->assertEquals('application/msword', MimeTypeResolver::resolve('antiguo.doc'));
        $this->assertEquals('text/plain; charset=utf-8', MimeTypeResolver::resolve('notas.txt'));
        $this->assertEquals('text/markdown; charset=utf-8', MimeTypeResolver::resolve('guia.md'));
    }

    public function test_resolves_audio_and_image_formats(): void
    {
        $this->assertEquals('audio/mpeg', MimeTypeResolver::resolve('audio.mp3'));
        $this->assertEquals('audio/wav', MimeTypeResolver::resolve('muestra.wav'));
        $this->assertEquals('image/png', MimeTypeResolver::resolve('portada.png'));
        $this->assertEquals('image/jpeg', MimeTypeResolver::resolve('foto.jpg'));
    }

    public function test_fallback_on_unknown_extension_or_null(): void
    {
        $this->assertEquals('application/octet-stream', MimeTypeResolver::resolve('binario.xyz'));
        $this->assertEquals('application/octet-stream', MimeTypeResolver::resolve(null));
        $this->assertEquals('application/custom', MimeTypeResolver::resolve('archivo.unknown', 'application/custom'));
    }
}
