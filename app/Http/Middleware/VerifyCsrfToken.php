<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    protected $except = [
        'finish', // Adicione a rota que você quer desabilitar a verificação CSRF
    ];

    protected function shouldSkip(Request $request)
{
    return in_array($request->route()->uri(), [
        'finish', // Rota que não deve passar pela verificação CSRF
    ]);
}

}
