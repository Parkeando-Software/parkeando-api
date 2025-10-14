<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name', // Nombre del rol: user, pro, admin, superadmin, worker, etc.
    ];

    /**
     * Relación: Un rol puede tener muchos usuarios asignados.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
