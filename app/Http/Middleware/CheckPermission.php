<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\Permission;

class CheckPermission
{


      /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        dd('Middleware CheckPermission chamado');
        
        $user = Auth::user();
        Log::info('Usuário autenticado:', ['user' => $user]);

        $routeName = $request->route()->getName();
        Log::info('Rota atual:', ['route' => $routeName]);

        $modules = Config::get('modules');
        Log::info('Configuração de módulos:', ['modules' => $modules]);

        $moduleName = null;
        foreach ($modules as $module => $pages) {
            if (array_key_exists($routeName, $pages)) {
                $moduleName = $module;
                break;
            }
        }
        Log::info('Módulo encontrado:', ['module' => $moduleName]);

        if (!$moduleName) {
            Log::error('Módulo não encontrado para a rota.');
            abort(403, 'Você não tem permissão para acessar esta página.');
        }

        $hasPermission = Permission::where('cargo', $user->cargo)
            ->where('module', $moduleName)
            ->where('page', $routeName)
            ->where('allowed', 1)
            ->exists();

        Log::info('Permissão verificada:', ['hasPermission' => $hasPermission]);

        if (!$hasPermission) {
            Log::error('Usuário não tem permissão.');
            abort(403, 'Você não tem permissão para acessar esta página.');
        }

        return redirect()->route('home')->with('error', 'Você não tem permissão para acessar essa página.');
    }
}
