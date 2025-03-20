<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL; // <-- Adicione isso
use App\Models\Atividade;

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
        // Forçar HTTPS em produção (adicione esse bloco aqui)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Compartilhar o usuário autenticado com todas as views
        View::share('user', Auth::user());

        // Registrar atividade como helper global
      
           
        
    }
}
