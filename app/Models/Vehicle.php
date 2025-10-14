<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * Atributos que se pueden asignar en masa.
     */
    protected $fillable = [
        'user_id',
        'plate',
        'brand',
        'model',
        'color',
        'is_default',
    ];

    /**
     * Relación inversa: un vehículo pertenece a un usuario (customer).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
