<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifySession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $activeSession = DB::table('sessions')->where('user_id', $userId)->exists();

            if (!$activeSession) {
                Auth::logout(); // Força o logout do usuário
                $request->session()->invalidate(); // Invalida a sessão
                $request->session()->regenerateToken(); // Gera um novo token CSRF

                return redirect()->route('login')->with('error', 'Sua sessão foi encerrada.');
            }
        }

        return $next($request);
    }
}
