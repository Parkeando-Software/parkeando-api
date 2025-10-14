<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'user_id',
        'type',
        'payment_method',
        'start_date',
        'end_date',
        'active',
    ];

    /**
     * Relación inversa: una suscripción pertenece a un usuario (customer).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
