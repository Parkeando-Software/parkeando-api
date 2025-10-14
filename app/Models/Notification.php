<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla.
     */
    protected $table = 'notifications';

    /**
     * Atributos asignables en masa.
     * 
     * - 'location' es geography(Point,4326) (PostGIS).
     * - Ya NO existen 'latitude'/'longitude' ni 'lat'/'lng' como columnas.
     */
    protected $fillable = [
        'user_id',
        'assigned_to_user_id',
        'in_minutes',
        'status',
        'assigned_at',
        'location', // geography(Point,4326)
        'blue_zone', // 🔵 nuevo campo
    ];

    /**
     * Ocultar campos que no queremos serializar en JSON.
     * - 'location' (geography) no es legible y puede aparecer como null/buffer.
     *   Las coordenadas se devuelven desde el controlador con ST_X/ST_Y.
     */
    protected $hidden = [
        'location',
    ];

    /**
     * Relaciones que se cargan siempre por defecto.
     */
    protected $with = ['user', 'assignedToUser', 'waitRequests'];

    /**
     * Casts de atributos.
     */
    protected $casts = [
        'status' => 'string',
        'blue_zone' => 'boolean', // 🔵 nuevo cast
    ];

    /* ======================================================
     |  RELACIONES
     |======================================================*/

    /**
     * Relación inversa: una notificación pertenece al liberador.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación inversa: una notificación puede estar asignada a un ocupante.
     */
    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Relación directa: una notificación puede tener muchas solicitudes de espera.
     */
    public function waitRequests()
    {
        return $this->hasMany(WaitRequest::class);
    }

    /* ======================================================
     |  HELPERS
     |======================================================*/

    /**
     * Helper: comprobar si la notificación está activa.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /* ======================================================
     |  SCOPES
     |======================================================*/

    /**
     * Scope: filtrar solo activas.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
