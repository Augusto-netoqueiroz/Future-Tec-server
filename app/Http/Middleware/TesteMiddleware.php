<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TesteMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('Middleware de teste executado!');

        return $next($request);
    }
}
