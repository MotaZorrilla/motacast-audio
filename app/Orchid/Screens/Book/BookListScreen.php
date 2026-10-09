<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Book;

use App\Actions\Book\DeleteBookAction;
use App\Models\Book;
use App\Orchid\Layouts\Book\BookListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class BookListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $totalDurationSec = Book::sum('total_duration') ?? 0;
        $totalHours = round($totalDurationSec / 3600, 1);

        return [
            'metrics' => [
                'total' => ['value' => number_format(Book::count())],
                'ready' => ['value' => number_format(Book::where('status', 'ready')->count())],
                'processing' => ['value' => number_format(Book::whereIn('status', ['pending', 'extracting', 'synthesizing'])->count())],
                'hours' => ['value' => $totalHours.' hrs'],
            ],
            'books' => Book::with('user')
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(15),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Gestión de Audiolibros';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Control maestro de conversiones, estados de síntesis y archivos multimedia en MotaCast.';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Subir Documento')
                ->icon('bs.cloud-arrow-up')
                ->url(route('books.create'))
                ->target('_blank'),

            Link::make('Ver Biblioteca Pública')
                ->icon('bs.collection-play')
                ->url(route('books.index'))
                ->target('_blank'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Total Documentos' => 'metrics.total',
                'Listos para Escuchar' => 'metrics.ready',
                'En Procesamiento' => 'metrics.processing',
                'Horas de Audio Generadas' => 'metrics.hours',
            ]),

            BookListLayout::class,
        ];
    }

    /**
     * Remove the book and all physical media files.
     */
    public function removeBook(Request $request, DeleteBookAction $action): void
    {
        $book = Book::findOrFail($request->get('id'));
        $title = $book->title;
        $action->execute($book);

        Toast::info("El audiolibro '{$title}' ha sido eliminado con éxito.");
    }
}
