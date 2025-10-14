<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitRequest extends Model
{
    use HasFactory;

    // Estados como constantes (evita string sueltos)
    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'wait_requests';

    protected $fillable = [
        'notification_id',
        'user_id',
        'status',
    ];

    /**
     * Al modificar una WaitRequest, toca la notificación para refrescar su updated_at.
     */
    protected $touches = ['notification'];

    /**
     * Cargas por defecto para evitar N+1 (ajústalo si te pesa en listados grandes).
     */
    protected $with = ['user', 'notification'];

    protected $casts = [
        'status' => 'string',
    ];

    /* =========================
     |  Relaciones
     |=========================*/

    public function notification()
    {
        return $this->belongsTo(Notification::class)->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /* =========================
     |  Query Scopes
     |=========================*/

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForNotification($query, int $notificationId)
    {
        return $query->where('notification_id', $notificationId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /* =========================
     |  Helpers
     |=========================*/

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
