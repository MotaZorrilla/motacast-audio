<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PdfExtractorService;
use App\Services\TtsTextNormalizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MasonicAndVoicePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_doc_file_extraction_succeeds_on_la_plomada(): void
    {
        $fixturePath = base_path('tests/fixtures/la_plomada.doc');
        $this->assertFileExists($fixturePath);

        $extractor = app(PdfExtractorService::class);
        $result = $extractor->extract($fixturePath);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['title']);
        $this->assertGreaterThan(0, $result['total_words']);
        $this->assertNotEmpty($result['chapters']);
        $this->assertStringContainsString('Plomada', $result['title']);
    }

    public function test_masonic_detection_and_abbreviation_expansion(): void
    {
        $normalizer = app(TtsTextNormalizerService::class);

        $masonicSample = <<<TXT
A L( G( D( G( A( D( U(
Ven( M(
Q( H(Ex - Ven(M(
QQ( HH( 1º y 2º VVig(
QQ( HH( VVisit(                             L( I( F(
QQ( HH( Todos
Plancha presentada por Octavian Pantea M(M(
Resp(Log(Hans Hauschildt Nº 175 - Or(de Ciudad Guayana
S(F(U(
TXT;

        $this->assertTrue($normalizer->isMasonicText($masonicSample));

        $normalized = $normalizer->normalize($masonicSample);

        $this->assertStringContainsString('A la Gloria del Gran Arquitecto del Universo', $normalized);
        $this->assertStringContainsString('Venerable Maestro', $normalized);
        $this->assertStringContainsString('Querido Hermano', $normalized);
        $this->assertStringContainsString('Queridos Hermanos Primer y Segundo Vigilantes', $normalized);
        $this->assertStringContainsString('Queridos Hermanos Visitadores', $normalized);
        $this->assertStringContainsString('Libertad, Igualdad, Fraternidad', $normalized);
        $this->assertStringContainsString('Queridos Hermanos Todos', $normalized);
        $this->assertStringContainsString('Maestro Masón', $normalized);
        $this->assertStringContainsString('Respetable Logia', $normalized);
        $this->assertStringContainsString('Oriente de Ciudad Guayana', $normalized);
        $this->assertStringContainsString('Salud, Fuerza, Unión', $normalized);
    }

    public function test_masonic_delta_unicode_abbreviations_expansion(): void
    {
        $normalizer = app(TtsTextNormalizerService::class);

        $unicodeSample = 'Q∴ H∴, el V∴ M∴ abre los trabajos con G∴ A∴ D∴ U∴ y da el T∴ A∴ F∴ a todos.';

        $this->assertTrue($normalizer->isMasonicText($unicodeSample));

        $normalized = $normalizer->normalize($unicodeSample);

        $this->assertStringContainsString('Querido Hermano', $normalized);
        $this->assertStringContainsString('Venerable Maestro', $normalized);
        $this->assertStringContainsString('Gran Arquitecto del Universo', $normalized);
        $this->assertStringContainsString('Triple Abrazo Fraternal', $normalized);
    }

    public function test_general_symbols_and_spanish_abbreviations(): void
    {
        $normalizer = app(TtsTextNormalizerService::class);

        $text = "• Ver art. 15 y cap. 2 en pág. 45.\nEl costo es 50 € o 60 $ (un 10% de descuento). [pic] Dr. Pérez dijo etc.";

        $normalized = $normalizer->normalize($text);

        $this->assertStringNotContainsString('•', $normalized);
        $this->assertStringNotContainsString('[pic]', $normalized);
        $this->assertStringContainsString('artículo 15', $normalized);
        $this->assertStringContainsString('capítulo 2', $normalized);
        $this->assertStringContainsString('página 45', $normalized);
        $this->assertStringContainsString('50 euros', $normalized);
        $this->assertStringContainsString('60 dólares', $normalized);
        $this->assertStringContainsString('10 por ciento', $normalized);
        $this->assertStringContainsString('Doctor Pérez', $normalized);
        $this->assertStringContainsString('etcétera', $normalized);
    }

    public function test_voice_preview_endpoint_returns_audio_stream(): void
    {
        $response = $this->get(route('voices.preview', ['voice' => 'es-VE-SebastianNeural']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'audio/mpeg');
        $response->assertHeader('Cache-Control', 'max-age=86400, public');
    }

    public function test_doc_file_upload_validation_and_submission(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'doc_tester@motazorrilla.com',
            'book_limit' => 5,
        ]);

        $fixturePath = base_path('tests/fixtures/la_plomada.doc');
        $uploadedDoc = new UploadedFile(
            $fixturePath,
            'la_plomada.doc',
            'application/msword',
            null,
            true
        );

        $response = $this->actingAs($user)->post(route('books.store'), [
            'pdf_file' => $uploadedDoc,
            'title' => 'La Plomada Autoanalisis',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'title' => 'La Plomada Autoanalisis',
            'user_id' => $user->id,
            'original_filename' => 'la_plomada.doc',
        ]);
    }
}
