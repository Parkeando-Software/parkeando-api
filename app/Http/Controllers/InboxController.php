<?php

namespace App\Http\Controllers;
use App\Models\UserInbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class InboxController extends Controller
{
    // GET /api/me/inbox → solo no leídos (máx. 20)
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $items = UserInbox::where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('id', 'asc')
            ->limit(20)
            ->get(['id','type','payload','created_at']);

        // ETag sencillo (reduce ancho de banda si no cambió nada)
        $etag = sha1($userId.'|'.optional($items->last())->id);
        if ($request->header('If-None-Match') === $etag) {
            return Response::make('', 304);
        }

        return response()
            ->json($items)
            ->header('ETag', $etag);
    }

    // POST /api/me/inbox/read → marca varios como leídos (batch)
    public function markRead(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        UserInbox::where('user_id', $request->user()->id)
            ->whereIn('id', $request->ids)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }
}
