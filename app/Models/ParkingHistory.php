<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ParkingHistory extends Model
{
    use HasFactory;

    public const TYPE_RELEASED = 'released';
    public const TYPE_OCCUPIED = 'occupied';

    protected $table = 'parking_history';

    /**
     * No existen columnas físicas latitude/longitude.
     * Se usan lat/lng virtuales para construir 'location' (PostGIS).
     */
    protected $fillable = [
        'user_id',
        'type',
        'in_minutes', // opcional
        'lat',        // virtual -> mutator
        'lng',        // virtual -> mutator
    ];

    protected $with = ['user'];

    protected $casts = [
        'type' => 'string',
    ];

    /* =========================
     |  Relaciones
     |=========================*/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* =========================
     |  Helpers
     |=========================*/
    public function isReleased(): bool
    {
        return $this->type === self::TYPE_RELEASED;
    }

    public function isOccupied(): bool
    {
        return $this->type === self::TYPE_OCCUPIED;
    }

    /* =========================
     |  Mutators (lat/lng -> location)
     |=========================*/
    public function setLatAttribute($value): void
    {
        // Guardar el valor para que esté disponible cuando llegue lng
        $this->attributes['lat'] = $value;

        if (!is_null($value) && isset($this->attributes['lng'])) {
            $lng = $this->attributes['lng'];
            $this->attributes['location'] = DB::raw(
                "ST_SetSRID(ST_MakePoint({$lng}, {$value}), 4326)::geography"
            );
        }
    }

    public function setLngAttribute($value): void
    {
        // Guardar el valor para que esté disponible cuando llegue lat
        $this->attributes['lng'] = $value;

        if (!is_null($value) && isset($this->attributes['lat'])) {
            $lat = $this->attributes['lat'];
            $this->attributes['location'] = DB::raw(
                "ST_SetSRID(ST_MakePoint({$value}, {$lat}), 4326)::geography"
            );
        }
    }

    /* =========================
     |  Accessors (location -> latitude/longitude)
     |=========================*/
    public function getLatitudeAttribute(): ?float
    {
        return $this->location
            ? (float) DB::table($this->table)
                ->selectRaw('ST_Y(location::geometry) as lat')
                ->where('id', $this->id)
                ->value('lat')
            : null;
    }

    public function getLongitudeAttribute(): ?float
    {
        return $this->location
            ? (float) DB::table($this->table)
                ->selectRaw('ST_X(location::geometry) as lng')
                ->where('id', $this->id)
                ->value('lng')
            : null;
    }
}
