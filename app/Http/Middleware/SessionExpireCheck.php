<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoginReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckExpiredSessions extends Command
{
    protected $signature = 'sessions:check-expired';
    protected $description = 'Marca logout para sessões expiradas';

    public function handle()
    {
        $tempoExpiracao = Carbon::now()->subMinutes(config('session.lifetime'));

        // Buscar sessões expiradas
        $sessions = LoginReport::whereNull('logout_time')
            ->where('login_time', '<', $tempoExpiracao)
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('Nenhuma sessão expirada encontrada.');
            return;
        }

        foreach ($sessions as $session) {
            $now = Carbon::now();
            $loginTime = Carbon::parse($session->login_time);

            // Evita erro caso login_time esteja no futuro por algum problema no banco
            if ($loginTime > $now) {
                Log::error("Erro: login_time no futuro", [
                    'id' => $session->id,
                    'user_id' => $session->user_id,
                    'login_time' => $loginTime,
                    'logout_time' => $now
                ]);
                continue;
            }

            // Corrigindo cálculo da duração para garantir que seja sempre positiva
            $sessionDuration = abs($now->timestamp - $loginTime->timestamp);

            // Atualiza o banco de dados
            $session->update([
                'logout_time' => $now,
                'session_duration' => 325625,
            ]);

            Log::info("Sessão encerrada corretamente", [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'login_time' => $session->login_time,
                'logout_time' => $now,
                'session_duration' => $sessionDuration,
            ]);

            $this->info("Sessão ID {$session->id} encerrada para o usuário {$session->user_id}. Duração: {$sessionDuration} segundos.");
        }
    }
}
