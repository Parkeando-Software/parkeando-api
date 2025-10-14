<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'role_id',             // Relación con la tabla roles
        'name',                // Nombre del usuario
        'email',               // Correo electrónico (único)
        'password',            // Contraseña encriptada
        'phone',               // Teléfono opcional
        'accept_terms',        // Aceptación de términos y condiciones
        'account_activated',   // Estado de activación de la cuenta
    ];

    /**
     * Atributos ocultos al serializar.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atributos que deben ser casteados.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'accept_terms'      => 'boolean',
        'account_activated' => 'boolean',
    ];

    // ─────────────── RELACIONES ───────────────

    /** Un usuario pertenece a un rol. */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** Un usuario puede tener una especialización como cliente. */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    /** Un usuario puede tener una especialización como trabajador. */
    public function worker(): HasOne
    {
        return $this->hasOne(Worker::class);
    }

    /** Un usuario puede tener una suscripción. */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /** Un usuario puede registrar múltiples vehículos. */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /** Un usuario puede generar varias notificaciones (liberar plaza). */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /** Un usuario puede solicitar esperar varias plazas. */
    public function waitRequests(): HasMany
    {
        return $this->hasMany(WaitRequest::class);
    }

    /** Un usuario puede tener múltiples entradas de historial de aparcamiento. */
    public function parkingHistory(): HasMany
    {
        return $this->hasMany(ParkingHistory::class);
    }

    /** Cuentas sociales (login con Google, etc.). */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }
}
