<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GlpiController; // Certifique-se de que o namespace esteja correto

class SendDailyTicketSummary extends Command
{
    // O nome e descrição do comando
    protected $signature = 'send:daily-ticket-summary';
    protected $description = 'Envia o resumo diário de tickets';

    // O controller que contém a lógica de envio
    protected $glpiController;

    public function __construct(GlpiController $glpiController)
    {
        parent::__construct();
        $this->glpiController = $glpiController;
    }

    // A lógica que será executada quando o comando for chamado
    public function handle()
    {
        $this->info('Enviando resumo diário de tickets...');

        try {
            // Chama a função do controller diretamente
            $this->glpiController->sendDailyTicketSummary();
            $this->info('Resumo enviado com sucesso!');
        } catch (\Exception $e) {
            $this->error('Erro ao enviar resumo: ' . $e->getMessage());
        }
    }
}
