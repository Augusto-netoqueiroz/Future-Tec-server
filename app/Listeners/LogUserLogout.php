<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LogUserLogout
{
    public function handle(Logout $event)
    {
        // Atualizando o registro de login com a hora de logout e a duração
        $loginLog = DB::table('login_logs')
            ->where('user_id', $event->user->id)
            ->whereNull('logout_time') // Certificando-se de que este é o login atual
            ->orderBy('login_time', 'desc') // Pegando o último login
            ->first();

        if ($loginLog) {
            // Garantir que login_time seja um objeto Carbon
            $loginTime = Carbon::parse($loginLog->login_time);
            $logoutTime = Carbon::now();

            // Log para verificar os valores
            Log::info("Login Time: " . $loginTime->toDateTimeString());
            Log::info("Logout Time: " . $logoutTime->toDateTimeString());

            // Verificar se a diferença é positiva
            $sessionDuration = $loginTime->diffInSeconds($logoutTime);

            // Log para ver o cálculo da duração
            Log::info("Session Duration (in seconds): " . $sessionDuration);

            // Se a diferença for negativa, definimos a duração como zero
            if ($sessionDuration < 0) {
                $sessionDuration = 0;
            }

            // Atualizando o logout e a duração da sessão
            DB::table('login_logs')
                ->where('id', $loginLog->id)
                ->update([
                    'logout_time' => $logoutTime,
                    'session_duration' => $sessionDuration,
                ]);
        }
    }
}
