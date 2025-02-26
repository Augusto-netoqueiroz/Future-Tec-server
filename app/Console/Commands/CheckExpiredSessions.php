<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoginReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\UserController;

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

        $userController = new UserController();

        foreach ($expiradas as $sessao) {
            $userId = $sessao->user_id;

            if (!$userId) {
                Log::warning("Sessão ID {$sessao->id} não tem um usuário associado.");
                continue;
            }

            Log::info("Chamando logoutUser para User ID {$userId}");

            // Chamar a função de logout do UserController passando o ID do usuário
            $userController->logoutsistema($userId);

            // Atualizar o registro no banco
            $sessao->logout_time = Carbon::now();
            $sessao->session_duration = $sessao->logout_time->diffInSeconds($sessao->login_time);
            $sessao->save();

            Log::info("Logout bem-sucedido para User ID {$userId}, sessão ID {$sessao->id}.");

            $this->info("Sessão encerrada: ID {$sessao->id}, User ID {$userId}, Login {$sessao->login_time}, Logout {$sessao->logout_time}, Duração {$sessao->session_duration} segundos.");
        }

        $this->info('Todas as sessões expiradas foram processadas.');
        Log::info('CheckExpiredSessions: Todas as sessões expiradas foram processadas.');
    }
}
