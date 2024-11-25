<?php

use Illuminate\Http\Request;
use App\Models\CallEvent;

Route::post('/save-event', function (Request $request) {
    $validated = $request->validate([
        'event_type' => 'required|string',
        'event_data' => 'required|string',
        'channel' => 'required|string',
    ]);

    $event = CallEvent::create([
        'event_type' => $validated['event_type'],
        'event_data' => $validated['event_data'],
        'channel' => $validated['channel'],
    ]);

    return response()->json(['message' => 'Evento salvo com sucesso!', 'data' => $event], 200);
});

Route::get('/call-events', [App\Http\Controllers\CallEventController::class, 'index']);
