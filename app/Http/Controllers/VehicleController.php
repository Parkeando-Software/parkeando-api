<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Http\Requests\StoreVehicleRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * Mostrar todos los vehículos del usuario autenticado.
     */
    public function index()
    {
        // Opcional: ordenar para que el predeterminado salga primero
        $vehicles = Auth::user()->vehicles()->orderByDesc('is_default')->latest()->get();
        return response()->json($vehicles);
    }

    /**
     * Registrar un nuevo vehículo para el usuario autenticado.
     */
    public function store(StoreVehicleRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = Auth::user();
            $data = $request->validated();

            // Si se crea como predeterminado, desmarcar los demás
            if (!empty($data['is_default']) && $data['is_default'] === true) {
                $user->vehicles()->update(['is_default' => false]);
            } else {
                // Si el usuario no tiene ningún predeterminado, el primero pasa a serlo
                $hasDefault = $user->vehicles()->where('is_default', true)->exists();
                if (!$hasDefault) {
                    $data['is_default'] = true;
                }
            }

            $vehicle = $user->vehicles()->create($data);

            return response()->json([
                'message' => 'Vehículo creado correctamente.',
                'vehicle' => $vehicle
            ], 201);
        });
    }

    /**
     * Actualizar un vehículo existente del usuario autenticado.
     */
    public function update(StoreVehicleRequest $request, Vehicle $vehicle)
    {
        // Verificar que el vehículo pertenece al usuario autenticado
        if ($vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return DB::transaction(function () use ($request, $vehicle) {
            $data = $request->validated();

            // Si este vehículo se marca como predeterminado, desmarcar todos los demás del usuario
            if (array_key_exists('is_default', $data) && $data['is_default'] === true) {
                Auth::user()
                    ->vehicles()
                    ->where('id', '!=', $vehicle->id)
                    ->update(['is_default' => false]);
            }

            $vehicle->update($data);

            return response()->json([
                'message' => 'Vehículo actualizado correctamente.',
                'vehicle' => $vehicle
            ]);
        });
    }

    /**
     * Eliminar un vehículo del usuario autenticado.
     */
    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $vehicle->delete();

        return response()->json(['message' => 'Vehículo eliminado correctamente.']);
    }
}
