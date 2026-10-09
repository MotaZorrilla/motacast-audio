<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Book;
use App\Models\SupportTicket;
use App\Models\TrafficLog;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Audiolibros')
                ->icon('bs.journal-text')
                ->title('MotaCast Core')
                ->route('platform.books')
                ->badge(fn () => Book::count()),

            Menu::make('Telemetría')
                ->icon('bs.graph-up')
                ->route('platform.telemetry')
                ->badge(fn () => TrafficLog::today()->count()),

            Menu::make('Mesa de Ayuda')
                ->icon('bs.chat-left-dots')
                ->route('platform.tickets')
                ->badge(fn () => SupportTicket::pending()->count(), Color::WARNING),

            Menu::make('Ir a la App Web')
                ->icon('bs.phone')
                ->title('Vistas Públicas')
                ->url(url('/books'))
                ->target('_blank'),

            Menu::make('Ver Landing Page')
                ->icon('bs.globe')
                ->url(url('/'))
                ->target('_blank')
                ->divider(),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Documentation')
                ->title('Docs')
                ->icon('bs.box-arrow-up-right')
                ->url('https://orchid.software/en/docs')
                ->target('_blank'),

            Menu::make('Changelog')
                ->icon('bs.box-arrow-up-right')
                ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
                ->target('_blank')
                ->badge(fn () => Dashboard::version(), Color::DARK),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
