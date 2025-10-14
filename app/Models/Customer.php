<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'user_id',     // Relación con el usuario base
        'points',      // Puntos acumulados del cliente
        'reputation',  // Valoración del cliente (de 0 a 5)
        'city',        // Ciudad del cliente (opcional)
    ];

    /**
     * Relación: Un customer pertenece a un usuario.
     * Relación 1:1 (user tiene una especialización como customer)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
