<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sippeer;
use App\Models\AgenteRamalVinculo;
use App\Models\Cdr;
use App\Models\QueueLog;
use App\Models\Queue; // Adicionar o modelo Queue
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Recebendo os filtros
        $userId = $request->input('user_id');
        $ramalId = $request->input('ramal_id');
        $queueName = $request->input('queue_name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Passo 1 - Buscar usuários
        $users = User::select('id', 'name')->get();

        // Passo 2 - Buscar ramais
        $ramais = Sippeer::where('modo', 'ramal')->select('id', 'name')->get();

        // Passo 3 - Buscar filas
        $filas = Queue::select('id', 'name')->get();

        // Passo 4 - Buscar vínculos de agentes com ramais no período
        $vinculos = AgenteRamalVinculo::with(['user', 'sippeer'])
            ->when($userId, function ($query) use ($userId) {
                $query->where('agente_id', $userId);
            })
            ->when($ramalId, function ($query) use ($ramalId) {
                $query->where('ramal_id', $ramalId);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('inicio_vinculo', [$startDate, $endDate]);
            })
            ->get();

        // Passo 5 - Buscar dados de chamadas (CDR) no período
        $chamadas = Cdr::when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('calldate', [$startDate, $endDate]);
        })
        ->when($ramalId, function ($query) use ($ramalId) {
            $query->where('src', $ramalId);
        })
        ->get();

        // Passo 6 - Buscar logs da fila (QueueLog)
        $queueLogs = QueueLog::when($queueName, function ($query) use ($queueName) {
            $query->where('queuename', $queueName);
        })
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('time', [$startDate, $endDate]);
        })
        ->get();

        // Calcular métricas do resumo
        $resumo = $this->calcularResumo($chamadas, $queueLogs);

        // Retornar dados para a view
        return view('dashboard.index', compact('users', 'ramais', 'filas', 'vinculos', 'resumo', 'queueLogs'));
    }

    private function calcularResumo($chamadas, $queueLogs)
    {
        $recebidas = $chamadas->where('disposition', 'ANSWERED')->count();
        $perdidas = $chamadas->where('disposition', 'NO ANSWER')->count();
        $efetuadas = $chamadas->whereNotNull('dst')->count();

        $tempoMedioAtendimento = round($chamadas->where('disposition', 'ANSWERED')->avg('billsec'), 2);
        $tempoMedioEspera = round($queueLogs->where('event', 'WAIT_TIME')->avg('duration'), 2);
        $sla = $this->calcularSLA($queueLogs);
        $nivelDeServico = $this->calcularNivelServico($queueLogs);

        return [
            'recebidas' => $recebidas,
            'perdidas' => $perdidas,
            'efetuadas' => $efetuadas,
            'tempo_medio_atendimento' => $tempoMedioAtendimento,
            'tempo_medio_ocupacao' => $tempoMedioEspera, // Ajustar conforme necessário
            'sla' => $sla,
            'nivel_servico' => $nivelDeServico,
        ];
    }

    private function calcularSLA($queueLogs)
    {
        $answered = $queueLogs->where('event', 'CONNECT')->count();
        $total = $queueLogs->count();
        return $total > 0 ? round(($answered / $total) * 100, 2) : 0;
    }

    private function calcularNivelServico($queueLogs)
    {
        $answeredWithinThreshold = $queueLogs->where('event', 'CONNECT')
            ->where('duration', '<=', 20) // Exemplo: 20 segundos de limite
            ->count();
        $total = $queueLogs->count();
        return $total > 0 ? round(($answeredWithinThreshold / $total) * 100, 2) : 0;
    }
}
