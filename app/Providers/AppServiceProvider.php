<?php

namespace App\Providers;



use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Atividade;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
{
    // Compartilhar o usuário autenticado com todas as views
    View::share('user', Auth::user());
    
    function registrarAtividade($acao, $descricao = null)
    {
        if (Auth::check()) {
            Atividade::create([
                'user_id' => Auth::id(),
                'acao' => $acao,
                'descricao' => $descricao,
                'ip' => Request::ip(),
            ]);
        }
    }


}
}
