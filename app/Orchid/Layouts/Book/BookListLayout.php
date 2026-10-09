<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Book;

use App\Models\Book;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class BookListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'books';

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id', 'ID')
                ->sort()
                ->width('70px'),

            TD::make('title', 'Título y Autor')
                ->sort()
                ->filter(Input::make())
                ->render(function (Book $book) {
                    $author = e($book->author ?: 'Desconocido');
                    $title = e($book->title);
                    $badge = $book->user_id
                        ? '<span class="badge bg-primary text-white ms-1">Usuario #'.$book->user_id.'</span>'
                        : '<span class="badge bg-secondary text-white ms-1">Invitado</span>';

                    return "<div><strong class='text-dark d-block'>{$title}</strong><small class='text-muted'>Por: {$author} {$badge}</small></div>";
                }),

            TD::make('voice', 'Voz')
                ->sort()
                ->render(fn (Book $book) => '<span class="badge bg-dark">'.e(str_replace(['es-VE-', 'es-MX-', 'es-ES-', 'es-AR-', 'es-CO-'], '', $book->voice)).'</span>'),

            TD::make('chapters', 'Capítulos')
                ->render(fn (Book $book) => "{$book->processed_chapters} / {$book->total_chapters}"),

            TD::make('total_duration', 'Duración')
                ->sort()
                ->render(fn (Book $book) => $book->formatted_duration),

            TD::make('status', 'Estado')
                ->sort()
                ->render(function (Book $book) {
                    $status = $book->status;
                    $class = match ($status) {
                        'ready' => 'bg-success',
                        'synthesizing', 'extracting' => 'bg-info',
                        'failed' => 'bg-danger',
                        default => 'bg-warning text-dark',
                    };
                    $label = $book->statusEnum()->label();

                    return "<span class='badge {$class}'>{$label}</span>";
                }),

            TD::make('created_at', 'Fecha')
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->sort(),

            TD::make('Acciones')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Book $book) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make('Abrir en Reproductor')
                            ->icon('bs.play-circle')
                            ->url(route('books.show', $book->id))
                            ->target('_blank'),

                        Link::make('Descargar Transcripción')
                            ->icon('bs.file-earmark-text')
                            ->url(route('books.transcription.download', $book->id))
                            ->target('_blank'),

                        Button::make('Eliminar')
                            ->icon('bs.trash3')
                            ->confirm('¿Deseas eliminar este audiolibro y todos sus archivos asociados?')
                            ->method('removeBook', [
                                'id' => $book->id,
                            ]),
                    ])),
        ];
    }
}
