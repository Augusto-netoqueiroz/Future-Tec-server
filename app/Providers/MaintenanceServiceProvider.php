<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MaintenanceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Adicione aqui qualquer configuração que você precise durante a manutenção.
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Registre quaisquer bindings de serviços, se necessário.
    }
}
