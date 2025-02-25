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

        // Busca as sessões que serão encerradas
        $expiradas = LoginReport::whereNull('logout_time')
            ->where('login_time', '<', $tempoExpiracao)
            ->get();

        if ($expiradas->isEmpty()) {
            $this->info('Nenhuma sessão expirada encontrada.');
            Log::info('CheckExpiredSessions: Nenhuma sessão expirada foi encontrada.');
            return;
        }

        foreach ($expiradas as $sessao) {
            $sessao->logout_time = Carbon::now();
            $sessao->session_duration = $sessao->logout_time->diffInSeconds($sessao->login_time);
            $sessao->save();

            $this->info("Sessão encerrada: ID {$sessao->id}, User ID {$sessao->user_id}, Login {$sessao->login_time}, Logout {$sessao->logout_time}, Duração {$sessao->session_duration} segundos.");
            Log::info("Sessão encerrada: ID {$sessao->id}, User ID {$sessao->user_id}, Login {$sessao->login_time}, Logout {$sessao->logout_time}, Duração {$sessao->session_duration} segundos.");
        }

        $this->info('Todas as sessões expiradas foram processadas.');
        Log::info('CheckExpiredSessions: Todas as sessões expiradas foram processadas.');
    }
}
