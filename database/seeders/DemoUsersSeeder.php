<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure real sample files exist on public disk
        if (Storage::disk('public')->exists('pdfs/manifiesto_homelab.pdf')) {
            if (!Storage::disk('public')->exists('pdfs/demo_alumno.pdf')) {
                Storage::disk('public')->copy('pdfs/manifiesto_homelab.pdf', 'pdfs/demo_alumno.pdf');
            }
            if (!Storage::disk('public')->exists('pdfs/demo_invitado.pdf')) {
                Storage::disk('public')->copy('pdfs/manifiesto_homelab.pdf', 'pdfs/demo_invitado.pdf');
            }
        }

        if (Storage::disk('public')->exists('audiobooks/1/chapter_1.mp3')) {
            if (!Storage::disk('public')->exists('audiobooks/demo_alumno_ch1.mp3')) {
                Storage::disk('public')->copy('audiobooks/1/chapter_1.mp3', 'audiobooks/demo_alumno_ch1.mp3');
            }
            if (!Storage::disk('public')->exists('audiobooks/demo_invitado_ch1.mp3')) {
                Storage::disk('public')->copy('audiobooks/1/chapter_1.mp3', 'audiobooks/demo_invitado_ch1.mp3');
            }
        }

        // 1. Alumno de MotaZorrilla (Cuota de 5 libros)
        $alumno = User::firstOrCreate(
            ['email' => 'alumno@motazorrilla.com'],
            [
                'name' => 'Carlos Estudiante',
                'password' => Hash::make('secret123'),
                'role' => 'user',
                'status' => 'active',
                'book_limit' => 5,
            ]
        );

        // Crear libro demo para el alumno si no tiene
        if ($alumno->books()->count() === 0) {
            $bookAlumno = Book::create([
                'user_id' => $alumno->id,
                'title' => 'Introducción a Redes Seguras y VPNs',
                'author' => 'Carlos Estudiante',
                'original_filename' => 'redes_seguras.pdf',
                'pdf_path' => 'pdfs/demo_alumno.pdf',
                'voice' => 'es-VE-SebastianNeural',
                'speed_rate' => '+0%',
                'pitch' => '+0Hz',
                'status' => 'ready',
                'total_chapters' => 1,
                'processed_chapters' => 1,
                'total_duration' => 12,
                'total_words' => 30,
            ]);

            Chapter::create([
                'book_id' => $bookAlumno->id,
                'chapter_number' => 1,
                'title' => 'Capítulo 1: Fundamentos de Cifrado',
                'content_text' => 'El cifrado de extremo a extremo protege la integridad de los paquetes en tránsito.',
                'audio_path' => 'audiobooks/demo_alumno_ch1.mp3',
                'duration_seconds' => 12,
                'word_count' => 30,
                'status' => 'ready',
            ]);
        }

        // 2. Usuario con cuota estándar de 1 libro (demostrar límite)
        $invitado = User::firstOrCreate(
            ['email' => 'invitado@motazorrilla.com'],
            [
                'name' => 'Invitado Especial',
                'password' => Hash::make('secret123'),
                'role' => 'user',
                'status' => 'active',
                'book_limit' => 1,
            ]
        );

        if ($invitado->books()->count() === 0) {
            $bookInvitado = Book::create([
                'user_id' => $invitado->id,
                'title' => 'Guía Rápida de Arquitectura Web',
                'author' => 'Invitado Especial',
                'original_filename' => 'guia_rapida.pdf',
                'pdf_path' => 'pdfs/demo_invitado.pdf',
                'voice' => 'es-MX-DaliaNeural',
                'speed_rate' => '+0%',
                'pitch' => '+0Hz',
                'status' => 'ready',
                'total_chapters' => 1,
                'processed_chapters' => 1,
                'total_duration' => 15,
                'total_words' => 35,
            ]);

            Chapter::create([
                'book_id' => $bookInvitado->id,
                'chapter_number' => 1,
                'title' => 'Capítulo 1: Principios de Microservicios',
                'content_text' => 'Los microservicios desacoplan la lógica del negocio facilitando el despliegue.',
                'audio_path' => 'audiobooks/demo_invitado_ch1.mp3',
                'duration_seconds' => 15,
                'word_count' => 35,
                'status' => 'ready',
            ]);
        }

        // 3. Usuario Suspendido (demostrar bloqueo preventivo)
        User::firstOrCreate(
            ['email' => 'suspendido@motazorrilla.com'],
            [
                'name' => 'Usuario Pausado',
                'password' => Hash::make('secret123'),
                'role' => 'user',
                'status' => 'suspended',
                'book_limit' => 3,
            ]
        );
    }
}
