<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Requests\StoreNotificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SearchNearbyRequest;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ParkingHistory;
// ───────────────────────────────────────────────────────────────
// Inbox (polling de toasts)
// ───────────────────────────────────────────────────────────────
use App\Models\UserInbox;

class NotificationController extends Controller
{
    /**
     * Mostrar todas las notificaciones activas.
     */
    public function index()
    {
        return DB::select("
            SELECT 
                id, 
                user_id, 
                in_minutes, 
                status,
                (ST_Y(location::geometry))::float8 AS latitude,
                (ST_X(location::geometry))::float8 AS longitude,
                created_at, 
                updated_at,
                blue_zone,                                   -- 🔵

                -- Instante exacto en que vence la notificación
                (created_at + (in_minutes || ' minutes')::interval) AS expires_at,

                -- Segundos efectivos hasta vencimiento (puede ser negativo si ya pasó)
                FLOOR(
                  EXTRACT(EPOCH FROM ((created_at + (in_minutes || ' minutes')::interval) - NOW()))
                )::int AS effective_seconds,

                -- Compatibilidad: tiempo restante clamp ≥ 0 (como antes)
                GREATEST(
                  FLOOR(EXTRACT(EPOCH FROM ((created_at + (in_minutes || ' minutes')::interval) - NOW()))),
                  0
                )::int AS remaining_seconds

            FROM notifications
            WHERE status = 'active'
            ORDER BY created_at DESC
        ");
    }

    /**
     * Mostrar los datos de una notificacion en concreto.
     */
    public function show($id)
    {
        $row = DB::selectOne("
            SELECT 
                n.id,
                n.user_id,

                -- Datos del creador
                u.name  AS user_name,
                u.email AS user_email,

                -- Datos base de la notificación
                n.in_minutes,
                n.status,
                (ST_Y(n.location::geometry))::float8 AS latitude,
                (ST_X(n.location::geometry))::float8 AS longitude,
                n.created_at,
                n.updated_at,
                n.blue_zone,                              -- 🔵

                -- Expiración y cuenta atrás
                (n.created_at + (n.in_minutes || ' minutes')::interval) AS expires_at,  -- instante exacto de vencimiento

                -- Segundos efectivos hasta vencimiento (puede ser negativo si ya pasó)
                FLOOR(
                  EXTRACT(EPOCH FROM ((n.created_at + (n.in_minutes || ' minutes')::interval) - NOW()))
                )::int AS effective_seconds,

                -- Compatibilidad (≥ 0)
                GREATEST(
                  FLOOR(EXTRACT(EPOCH FROM ((n.created_at + (n.in_minutes || ' minutes')::interval) - NOW()))),
                  0
                )::int AS remaining_seconds,

                -- Nº de solicitudes de espera
                COALESCE(w.cnt, 0) AS wait_requests_count,

                -- Vehículo preferido del usuario 
                vsel.vehicle_id,
                vsel.plate      AS vehicle_plate,
                vsel.brand      AS vehicle_brand,
                vsel.model      AS vehicle_model,
                vsel.color      AS vehicle_color,
                vsel.is_default AS vehicle_is_default

            FROM notifications n
            JOIN users u ON u.id = n.user_id

            -- contar wait_requests
            LEFT JOIN (
                SELECT notification_id, COUNT(*)::int AS cnt
                FROM wait_requests
                GROUP BY notification_id
            ) w ON w.notification_id = n.id

            -- elegir un vehículo del usuario: primero el predeterminado; si no hay, cualquiera
            LEFT JOIN LATERAL (
                SELECT v.id AS vehicle_id, v.plate ,v.brand, v.model, v.color, v.is_default
                FROM vehicles v
                WHERE v.user_id = n.user_id
                ORDER BY v.is_default DESC, v.id ASC
                LIMIT 1
            ) vsel ON true

            WHERE n.id = ?
            LIMIT 1
        ", [$id]);

        if (!$row) {
            return response()->json(['message' => 'Notificación no encontrada.'], 404);
        }

        return response()->json($row);
    }

    /**
     * Crear una nueva notificación (usuario libera plaza).
     */
    public function store(StoreNotificationRequest $request)
    {
        $lat = (float) $request->input('lat');
        $lng = (float) $request->input('lng');

        // Inserta creando la geography(Point,4326) directamente
        $id = DB::table('notifications')->insertGetId([
            'user_id'    => auth()->id(),
            'in_minutes' => (int) $request->input('in_minutes'),
            'status'     => 'active',
            'blue_zone'  => (bool) $request->input('blue_zone', false),   // 🔵
            'location'   => DB::raw("ST_SetSRID(ST_MakePoint($lng, $lat), 4326)::geography"),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vuelve a leer con lat/lng derivados (para el front)
        $row = DB::selectOne("
        SELECT 
            id, 
            user_id, 
            in_minutes, 
            status,
            (ST_Y(location::geometry))::float8 AS latitude,
            (ST_X(location::geometry))::float8 AS longitude,
            created_at, 
            updated_at,
            blue_zone                              -- 🔵
        FROM notifications
        WHERE id = ?
        ", [$id]);

        // ─────────────────────────────────────────────
        // Registrar en historial de parkings (released)
        // ─────────────────────────────────────────────
        try {
            Log::info('PH:create:attempt', [
                'user_id' => auth()->id(),
                'type'    => ParkingHistory::TYPE_RELEASED,
                'lat'     => $row->latitude ?? null,
                'lng'     => $row->longitude ?? null,
            ]);

            $entry = ParkingHistory::create([
                'user_id' => auth()->id(),
                'type'    => ParkingHistory::TYPE_RELEASED, // 'released'
                'lat'     => $row->latitude,
                'lng'     => $row->longitude,
            ]);

            Log::info('PH:create:ok', ['id' => $entry->id ?? null]);
        } catch (\Throwable $e) {
            Log::error('PH:create:error', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Fallback crudo por si el modelo fallara (mutators/casts/etc.)
            DB::insert("
                INSERT INTO parking_history (user_id, type, location, created_at, updated_at)
                VALUES (?, 'released',
                        ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
                        NOW(), NOW()
                )
            ", [auth()->id(), $row->longitude, $row->latitude]);

            Log::info('PH:raw-insert:ok');
        }

        return response()->json([
            'message' => 'Plaza liberada correctamente.',
            'data'    => $row,
        ], 201);
    }

    /**
     * Marcar notificación como ocupada o expirada.
     */
    public function update(Request $request, Notification $notification)
    {
        $request->validate([
            // OJO: 'occupied' solo se alcanza vía POST /notifications/{id}/confirm
            'status' => 'required|in:active,assigned,expired',
        ]);

        // ─────────────────────────────────────────────────────────
        // ASIGNAR: active -> assigned (al usuario autenticado)
        // ─────────────────────────────────────────────────────────
        if ($request->status === 'assigned') {
            // No auto-asignarse
            if ((int) $notification->user_id === (int) Auth::id()) {
                return response()->json(['message' => 'No puedes asignarte tu propia plaza.'], 403);
            }

            // Idempotencia: ya estaba asignada a mí
            if ($notification->status === 'assigned'
                && (int) $notification->assigned_to_user_id === (int) Auth::id()) {
                return response()->json([
                    'message' => 'La plaza ya estaba asignada a ti.',
                    'data'    => $notification,
                ]);
            }

            // Solo se puede asignar si está activa
            if ($notification->status !== 'active') {
                return response()->json(['message' => 'Solo notificaciones activas pueden asignarse.'], 422);
            }

            $notification->update([
                'status'               => 'assigned',
                'assigned_to_user_id'  => Auth::id(),
                'assigned_at'          => now(),   // 👈 importante para auto-expirar después
            ]);

            // ─────────────────────────────────────────────────────────
            // Inbox: avisar al LIBERADOR que alguien va hacia su plaza
            // (type = 'assigned_notice'). Se ejecuta tras confirmar commit.
            // ─────────────────────────────────────────────────────────
            DB::afterCommit(function () use ($notification) {
                // Vehículo del usuario que se asignó la plaza (quien va hacia la plaza)
                $vehicle = DB::selectOne("
                    SELECT v.plate, v.brand, v.model, v.color
                    FROM vehicles v
                    WHERE v.user_id = ?
                    ORDER BY v.is_default DESC, v.id ASC
                    LIMIT 1
                ", [Auth::id()]);

                // Coordenadas actuales de la notificación (opcional en payload)
                $coords = DB::selectOne("
                    SELECT 
                        (ST_Y(location::geometry))::float8 AS latitude,
                        (ST_X(location::geometry))::float8 AS longitude
                    FROM notifications
                    WHERE id = ?
                    LIMIT 1
                ", [$notification->id]);

                // Crear ficha de inbox para el liberador (creador de la notificación)
                UserInbox::create([
                    'user_id' => (int) $notification->user_id,
                    'type'    => 'assigned_notice',
                    'payload' => [
                        'notification_id'  => (int) $notification->id,
                        'assignee_user_id' => (int) Auth::id(),
                        'assigned_at'      => now()->toISOString(),
                        'vehicle'          => [
                            'plate' => $vehicle->plate ?? null,
                            'brand' => $vehicle->brand ?? null,
                            'model' => $vehicle->model ?? null,
                            'color' => $vehicle->color ?? null,
                        ],
                        'location'         => [
                            'lat' => $coords->latitude  ?? null,
                            'lng' => $coords->longitude ?? null,
                        ],
                        // Mensaje listo para mostrar (compatibilidad)
                        'message'          => sprintf(
                            'Alguien va hacia tu plaza: %s %s %s (%s).',
                            $vehicle->brand ?? 'Vehículo',
                            $vehicle->model ?? '',
                            $vehicle->color ?? '',
                            $vehicle->plate ?? 'matrícula N/D'
                        ),
                    ],
                ]);
            });

            return response()->json([
                'message' => 'Plaza asignada correctamente.',
                'data'    => $notification,
            ]);
        }

        // ─────────────────────────────────────────────────────────
        // NO DISPONIBLE: assigned -> expired (lo marca el propio asignado)
        // ─────────────────────────────────────────────────────────
        if ($request->status === 'expired') {
            // Solo puede expirar quien la tiene asignada
            if (!$notification->assigned_to_user_id
                || (int) $notification->assigned_to_user_id !== (int) Auth::id()) {
                return response()->json(['message' => 'No puedes expirar una plaza no asignada a ti.'], 403);
            }

            // Solo tiene sentido si está assigned
            if ($notification->status !== 'assigned') {
                return response()->json(['message' => 'Solo plazas asignadas pueden marcarse como no disponibles.'], 422);
            }

            $notification->update([
                'status'     => 'expired',
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Plaza marcada como no disponible.',
                'data'    => $notification,
            ]);
        }

        // (Opcional) permitir otros cambios válidos si los necesitas.
        // Por defecto, no volvemos a 'active' desde aquí.
        return response()->json([
            'message' => 'Solicitud de cambio de estado no permitida.',
        ], 422);
    }

    public function searchNearby(SearchNearbyRequest $request)
    {
        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $radius = 500; // en metros
        $userId = auth()->id(); // ← id del usuario actual

        $notifications = DB::select("
            SELECT 
                id, 
                user_id, 
                in_minutes, 
                status,
                (ST_Y(location::geometry))::float8 AS latitude,
                (ST_X(location::geometry))::float8 AS longitude,
                blue_zone,                                 -- 🔵

                ST_Distance(
                    location,
                    ST_SetSRID(ST_MakePoint(?, ?), 4326)
                )::float8 AS distance_meters,

                -- Instante exacto en que vence la notificación
                (created_at + (in_minutes || ' minutes')::interval) AS expires_at,

                -- Segundos efectivos hasta vencimiento (puede ser negativo si ya pasó)
                FLOOR(
                  EXTRACT(EPOCH FROM ((created_at + (in_minutes || ' minutes')::interval) - NOW()))
                )::int AS effective_seconds,

                -- Compatibilidad: tiempo restante clamp ≥ 0 (como antes)
                GREATEST(
                  FLOOR(EXTRACT(EPOCH FROM ((created_at + (in_minutes || ' minutes')::interval) - NOW()))),
                  0
                )::int AS remaining_seconds

            FROM notifications
            WHERE status = 'active'
              AND location IS NOT NULL
              AND ST_DWithin(
                    location,
                    ST_SetSRID(ST_MakePoint(?, ?), 4326),
                    ?
                  )
              AND user_id IS DISTINCT FROM ?   -- ← evita devolver tus propias plazas
            ORDER BY distance_meters ASC
        ", [$lng, $lat, $lng, $lat, $radius, $userId]);

        return response()->json($notifications);
    }

    /**Metedo que confirma plaza ocupada y asigna puntos */
    public function confirm(Request $request, $id)
    {
        $pointsToAward = config('parking.points_on_confirm', 10);

        // 1) Lógica crítica y puntos: todo en transacción
        $result = DB::transaction(function () use ($id, $pointsToAward) {
            // Bloqueo pesimista de la fila
            $notification = DB::selectOne("
                SELECT id, user_id, assigned_to_user_id, status
                FROM notifications
                WHERE id = ?
                FOR UPDATE
            ", [$id]);

            if (!$notification) {
                return ['response' => response()->json(['message' => 'Notificación no encontrada.'], 404)];
            }

            // Debe estar ASIGNADA
            if ($notification->status !== 'assigned') {
                return ['response' => response()->json(['message' => 'La notificación no está asignada.'], 422)];
            }

            // Solo puede confirmar quien se la asignó
            if ((int) $notification->assigned_to_user_id !== (int) Auth::id()) {
                return ['response' => response()->json(['message' => 'No puedes confirmar una plaza asignada a otro usuario.'], 403)];
            }

            // Evitar auto-premio
            if ((int) $notification->user_id === (int) Auth::id()) {
                return ['response' => response()->json(['message' => 'No puedes confirmar tu propia notificación.'], 403)];
            }

            // Premiar al liberador
            /** @var \App\Models\Customer|null $releaser */
            $releaser = Customer::where('user_id', $notification->user_id)
                ->lockForUpdate()
                ->first();

            if (!$releaser) {
                return ['response' => response()->json(['message' => 'El usuario que liberó no tiene perfil de cliente.'], 422)];
            }

            // Incrementar puntos
            $releaser->increment('points', $pointsToAward);

            // Recalcular reputación: +5 por cada 100 puntos (tope 100)
            $levels = intdiv($releaser->points, 100);
            $newReputation = min(5 + ($levels * 5), 100);

            if ((float) $releaser->reputation !== (float) $newReputation) {
                $releaser->reputation = $newReputation;
                $releaser->save();
            }

            // Marcar notificación como ocupada
            DB::update("
                UPDATE notifications
                SET status = 'occupied', updated_at = NOW()
                WHERE id = ?
            ", [$notification->id]);

            // Tomar coordenadas para el historial (las devolvemos para usarlas FUERA de la transacción)
            $coords = DB::selectOne("
                SELECT 
                    (ST_Y(location::geometry))::float8 AS latitude,
                    (ST_X(location::geometry))::float8 AS longitude
                FROM notifications
                WHERE id = ?
                LIMIT 1
            ", [$notification->id]);

            // Respuesta EXACTA como antes
            $response = response()->json([
                'message' => 'Plaza confirmada. Puntos asignados al usuario que liberó.',
                'notification_id'      => (int) $notification->id,
                'awarded_points'       => $pointsToAward,
                'releaser_user_id'     => (int) $notification->user_id,
                'assigned_to_user_id'  => (int) $notification->assigned_to_user_id,
                'releaser' => [
                    'customer_id' => $releaser->id,
                    'user_id'     => $releaser->user_id,
                    'points'      => $releaser->points,
                    'reputation'  => $releaser->reputation,
                ],
            ], 200);

            // ─────────────────────────────────────────────────────────
            // IMPORTANTE: ampliamos el array de retorno interno para usar
            // estos datos FUERA de la transacción (no afecta a la respuesta).
            // ─────────────────────────────────────────────────────────
            return [
                'response'           => $response,
                'lat'                => $coords->latitude ?? null,
                'lng'                => $coords->longitude ?? null,
                'notification_id'    => (int) $notification->id,
                'releaser_user_id'   => (int) $notification->user_id,
                'releaser'           => [
                    'points'     => $releaser->points,
                    'reputation' => $releaser->reputation,
                ],
            ];
        });

        // 2) Registro en parking_history (NO crítico): fuera de la transacción
        if (isset($result['lat'], $result['lng'])) {
            try {
                ParkingHistory::create([
                    'user_id' => Auth::id(),                       // quien ocupa
                    'type'    => ParkingHistory::TYPE_OCCUPIED,    // 'occupied'
                    'lat'     => $result['lat'],
                    'lng'     => $result['lng'],
                ]);
            } catch (\Throwable $e) {
                Log::error('PH:occupied:post-confirm:error', [
                    'msg'  => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
                // Fallback opcional: SQL crudo que NO interrumpe la respuesta
                try {
                    DB::insert("
                        INSERT INTO parking_history (user_id, type, location, created_at, updated_at)
                        VALUES (?, 'occupied',
                                ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
                                NOW(), NOW()
                        )
                    ", [Auth::id(), $result['lng'], $result['lat']]);
                } catch (\Throwable $e2) {
                    Log::error('PH:occupied:fallback-insert:error', ['msg' => $e2->getMessage()]);
                }
            }
        }

        // ─────────────────────────────────────────────────────────
        // Inbox: avisar al LIBERADOR de los puntos ganados (type='points_awarded')
        // Se ejecuta tras el commit de la transacción anterior.
        // ─────────────────────────────────────────────────────────
        DB::afterCommit(function () use ($result, $pointsToAward) {
            try {
                UserInbox::create([
                    'user_id' => (int) $result['releaser_user_id'],
                    'type'    => 'points_awarded',
                    'payload' => [
                        'message'         => "¡Has ganado +{$pointsToAward} puntos!",
                        'awarded_points'  => $pointsToAward,
                        'total_points'    => $result['releaser']['points']    ?? null,
                        'reputation'      => $result['releaser']['reputation'] ?? null,
                        'notification_id' => (int) $result['notification_id'],
                        'by_user_id'      => (int) Auth::id(),
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::error('Inbox:points_awarded:error', ['msg' => $e->getMessage()]);
            }
        });

        // 3) Devolver lo de siempre
        return $result['response'];
    }

}
