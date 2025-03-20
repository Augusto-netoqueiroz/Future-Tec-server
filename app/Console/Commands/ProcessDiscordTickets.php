<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GlpiController;
use Illuminate\Support\Facades\Log;

class ProcessDiscordTickets extends Command
{
    protected $signature   = 'glpi:process-discord';
    protected $description = 'Processa as mensagens do Discord armazenadas no banco e cria tickets no GLPI';

    public function handle()
    {
        $this->info('Iniciando processAndCreateTickets...');
        Log::info('[ProcessDiscordTickets] Iniciando processAndCreateTickets');

        // Invocamos o controller
        $controller = app(GlpiController::class);

        // A controller retorna um JsonResponse
        $response = $controller->processAndCreateTickets();

        // Pegamos o conteúdo JSON para exibir no console e logar
        $content = $response->getContent();
        $this->line('Retorno: ' . $content);

        // Também salvamos esse conteúdo nos logs
        Log::info('[ProcessDiscordTickets] Retorno do método processAndCreateTickets', [
            'content' => json_decode($content, true)
        ]);

        $this->info('Finalizado.');
        Log::info('[ProcessDiscordTickets] Finalizado.');

        return 0;
    }
}
