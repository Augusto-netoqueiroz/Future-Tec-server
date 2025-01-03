<?php

use Illuminate\Auth\Events\Login;
use App\Listeners\LogUserLogin;
use Illuminate\Auth\Events\Logout;
use App\Listeners\LogUserLogout;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogUserLogin::class,
        ],
        Logout::class => [
            LogUserLogout::class, // Listener para o logout
        ],
    ];
}
