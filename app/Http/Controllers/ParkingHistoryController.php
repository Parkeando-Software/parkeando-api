<?php

namespace App\Http\Controllers;

use App\Models\ParkingHistory;
use App\Http\Requests\StoreParkingHistoryRequest;

class ParkingHistoryController extends Controller
{
    /**
     * Historial del usuario autenticado (más reciente primero).
     */
    public function index()
    {
        return ParkingHistory::where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    /**
     * Registrar un evento en el historial (released/occupied).
     * Espera: lat, lng, type
     */
    public function store(StoreParkingHistoryRequest $request)
    {
        $entry = ParkingHistory::create([
            'user_id' => auth()->id(),
            'type'    => $request->type,   // 'released' | 'occupied'
            'lat'     => $request->lat,    // mutator construye 'location'
            'lng'     => $request->lng,    // mutator construye 'location'
        ]);

        return response()->json([
            'message' => 'Acción registrada en el historial.',
            'data'    => $entry,
        ], 201);
    }
}
