<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeleteRequest extends Model
{
    use HasFactory, HasUuids;

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'user_email',
        'reason',
        'additional_info',
        'status',
        'confirmation_token',
        'processed_at',
        'confirmed_at',
        'cancelled_at',
    ];

    /**
     * Atributos que deben ser casteados.
     */
    protected $casts = [
        'processed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Motivos válidos para eliminación de cuenta.
     */
    public const REASONS = [
        'privacy' => 'Privacidad',
        'no_use' => 'No uso la aplicación',
        'dissatisfied' => 'No estoy satisfecho',
        'duplicate' => 'Cuenta duplicada',
        'other' => 'Otro motivo',
        'dashboard_request' => 'Solicitud desde dashboard',
    ];

    /**
     * Estados válidos para la solicitud.
     */
    public const STATUSES = [
        'pending' => 'Pendiente',
        'processing' => 'Procesando',
        'completed' => 'Completada',
        'cancelled' => 'Cancelada',
    ];

    /**
     * Relación con el usuario por email.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_email', 'email');
    }

    /**
     * Scope para solicitudes pendientes.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para solicitudes confirmadas.
     */
    public function scopeConfirmed($query)
    {
        return $query->whereNotNull('confirmed_at');
    }

    /**
     * Scope para solicitudes canceladas.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Verificar si la solicitud está pendiente.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar si la solicitud está confirmada.
     */
    public function isConfirmed(): bool
    {
        return !is_null($this->confirmed_at);
    }

    /**
     * Verificar si la solicitud está cancelada.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Verificar si la solicitud puede ser cancelada.
     */
    public function canBeCancelled(): bool
    {
        return $this->isPending() && !$this->isConfirmed();
    }

    /**
     * Marcar como confirmada.
     */
    public function markAsConfirmed(): void
    {
        $this->update([
            'status' => 'processing',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Marcar como cancelada.
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Marcar como completada.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }
}
