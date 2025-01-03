<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class LogUserLogin
{
    public function handle(Login $event)
    {
        // Salvando o login na tabela login_logs
        DB::table('login_logs')->insert([
            'user_id' => $event->user->id,
            'ip_address' => Request::ip(),
            'login_time' => now(),
        ]);
    }
}
