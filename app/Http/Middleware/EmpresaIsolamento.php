<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmpresaIsolamento
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está logado e tem um `empresa_id`
        if (auth()->check()) {
            // Armazena o `empresa_id` do usuário na sessão
            session(['empresa_id' => auth()->user()->empresa_id]);
        }

        return $next($request);
    }
}
