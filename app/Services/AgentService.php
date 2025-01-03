<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;  // Para fazer a requisição HTTP ao Node.js

class AgentService
{
    // Função para pausar/despausar o agente
    public function pauseAgent($agent, $paused, $reason = null)
{
    // Verifica se os parâmetros são válidos
    if (!$agent || is_null($paused)) {
        return [
            'success' => false,
            'message' => 'Os parâmetros "agent" e "paused" são obrigatórios.'
        ];
    }

    // Define o estado de pausa
    $pauseStatus = $paused ? true : false; // AMI aceita 'true' ou 'false'

    // Envia a requisição HTTP para o servidor Node.js
    $response = Http::post('http://93.127.212.237:3000/pause-agent', [
        'agent' => $agent,        // Nome do agente (exemplo: 'SIP/1001')
        'paused' => $pauseStatus, // Estado de pausa: true ou false
        'reason' => $reason ?? '' // Motivo opcional
    ]);

    if ($response->successful()) {
        return [
            'success' => true,
            'message' => 'Ação de pausa/despausa realizada com sucesso.',
            'data' => $response->json()
        ];
    }

    return [
        'success' => false,
        'message' => 'Erro ao pausar/despausar o agente.',
        'error' => $response->json()
    ];
}
    

    }