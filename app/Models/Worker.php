<?php





namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * Relación inversa: un worker pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
