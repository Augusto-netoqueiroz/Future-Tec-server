<?php

namespace App\Http\Controllers;

use App\Models\CallEvent;

class CallEventController extends Controller
{
    public function index()
    {
        // Pega todos os eventos do banco de dados
        $events = CallEvent::all();
        
        // Retorna a view com os eventos
        return view('call_events.index', compact('events'));
    }
}
