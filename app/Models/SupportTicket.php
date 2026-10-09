<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Screen\AsSource;

class SupportTicket extends Model
{
    use AsSource, Filterable, HasFactory;

    protected $fillable = [
        'user_id',
        'guest_email',
        'type',
        'subject',
        'message',
        'status',
        'requested_books',
        'admin_reply',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'requested_books' => 'integer',
    ];

    protected $allowedFilters = [
        'id' => Where::class,
        'subject' => Like::class,
        'type' => Where::class,
        'status' => Where::class,
        'created_at' => WhereDateStartEnd::class,
    ];

    protected $allowedSorts = [
        'id',
        'subject',
        'type',
        'status',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pendiente';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resuelto';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pendiente');
    }

    public function scopeExtensions($query)
    {
        return $query->where('type', 'extension_limite');
    }

    public function getFormattedTypeAttribute(): string
    {
        return match ($this->type) {
            'extension_limite' => 'Solicitud de Cuota',
            'bug' => 'Reporte de Bug',
            'sugerencia' => 'Sugerencia Beta',
            default => 'Consulta',
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'resuelto' => 'bg-emerald-500/15 text-emerald-600 dark:text-[#00ff87] border-emerald-500/30',
            'rechazado' => 'bg-rose-500/15 text-rose-600 dark:text-rose-400 border-rose-500/30',
            'en_revision' => 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border-cyan-500/30',
            default => 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30',
        };
    }
}
