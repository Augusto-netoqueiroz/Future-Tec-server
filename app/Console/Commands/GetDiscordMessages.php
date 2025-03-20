<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GlpiController;
use Illuminate\Support\Facades\Log;

class GetDiscordMessages extends Command
{
    protected $signature   = 'glpi:get-discord';
    protected $description = 'Busca as mensagens do Discord e salva no banco local';

    public function handle()
    {
        $this->info('Iniciando getDiscordMessages...');
        Log::info('[GetDiscordMessages] Iniciando método getDiscordMessages');

        // Chamamos o Controller (ou Service) responsável
        $controller = app(GlpiController::class);

        $response = $controller->getDiscordMessages();

        // Resposta da controller (JsonResponse), podemos exibir no console:
        $jsonContent = $response->getData();
        $this->line('Retorno: ' . json_encode($jsonContent, JSON_PRETTY_PRINT));

        // E também logamos no arquivo
        Log::info('[GetDiscordMessages] Mensagens do Discord processadas', [
            'response' => $jsonContent
        ]);

        $this->info('Finalizado.');
        Log::info('[GetDiscordMessages] Finalizado.');

        return 0;
    }
}
