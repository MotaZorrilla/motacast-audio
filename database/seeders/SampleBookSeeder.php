<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class SampleBookSeeder extends Seeder
{
    public function run(): void
    {
        Book::create([
            'title' => 'El Manifiesto de la Ingeniería Homelab',
            'author' => 'Héctor Mota Zorrilla',
            'description' => 'Tratado técnico sobre soberanía de datos, infraestructura propia y modelos de voz neuronal en producción.',
            'original_filename' => 'manifiesto_homelab.pdf',
            'pdf_path' => 'pdfs/manifiesto_homelab.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'pending',
        ]);
    }
}
