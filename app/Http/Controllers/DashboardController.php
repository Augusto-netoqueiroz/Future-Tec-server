<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sippeer;
use App\Models\AgenteRamalVinculo;
use App\Models\Cdr;
use App\Models\QueueLog;
use App\Models\Queue;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Verifica se o usuário está autenticado
        if (!auth()->check()) {
            return redirect()->route('login'); // ou return view('auth.login') se quiser exibir diretamente
        }
    
        $user = auth()->user();
        $empresaId = $user->empresa_id;
    
        $userId = $request->input('user_id');
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
    
        $users = User::where('empresa_id', $empresaId)->select('id', 'name')->get();
        $ramais = Sippeer::where('modo', 'ramal')->where('empresa_id', $empresaId)->select('id', 'name')->get();
        $filas = Queue::where('empresa_id', $empresaId)->select('id', 'name')->get();
    
        $vinculos = AgenteRamalVinculo::whereHas('user', function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId);
            })
            ->when($userId, function ($query) use ($userId) {
                $query->where('agente_id', $userId);
            })
            ->get();
    
        $resumo = $this->calcularResumo($vinculos, $startDate, $endDate);
        $resumoFilas = $this->calcularResumoFilas($empresaId, $startDate, $endDate);
    
        return view('dashboard.index', compact('users', 'ramais', 'filas', 'vinculos', 'resumo', 'resumoFilas'));
    }
    

    private function calcularResumo($vinculos, $startDate, $endDate)
    {
        $resumo = [];
        $dadosUsuarios = [];

        foreach ($vinculos as $vinculo) {
            $ramal = $vinculo->sippeer->name;
            $userName = $vinculo->user->name;

            if (!isset($dadosUsuarios[$userName])) {
                $dadosUsuarios[$userName] = [
                    'usuario' => $userName,
                    'recebidas' => 0,
                    'perdidas' => 0,
                    'efetuadas' => 0,
                ];
            }

            $dadosUsuarios[$userName]['recebidas'] += Cdr::where('dst', $ramal)->where('disposition', 'ANSWERED')
                ->whereBetween('calldate', [$startDate, $endDate])->count();

            $dadosUsuarios[$userName]['perdidas'] += Cdr::where('dst', $ramal)->where('disposition', 'NO ANSWER')
                ->whereBetween('calldate', [$startDate, $endDate])->count();

            $dadosUsuarios[$userName]['efetuadas'] += Cdr::where('src', $ramal)
                ->whereBetween('calldate', [$startDate, $endDate])->count();
        }

        return array_values($dadosUsuarios);
    }

    private function calcularResumoFilas($empresaId, $startDate, $endDate)
    {
        // Define o tempo limite para calcular o SLA (em segundos)
        $slaLimit = 20; 
    
        return QueueLog::join('queues', 'queue_log.queuename', '=', \DB::raw("queues.queue_name COLLATE utf8mb4_unicode_ci"))
            ->where('queues.empresa_id', $empresaId)
            ->whereBetween('queue_log.time', [$startDate, $endDate])
            ->selectRaw('
                queues.name as fila,
                COUNT(CASE WHEN queue_log.event = "ENTERQUEUE" THEN 1 END) as total_recebidas,
                COUNT(CASE WHEN queue_log.event = "CONNECT" THEN 1 END) as atendidas,
                COUNT(CASE WHEN queue_log.event = "ABANDON" THEN 1 END) as abandonadas,
                COUNT(CASE 
                    WHEN queue_log.event = "CONNECT" 
                    AND CAST(SUBSTRING_INDEX(queue_log.data, "|", 1) AS UNSIGNED) <= ? 
                THEN 1 END) * 100.0 
                / NULLIF(COUNT(CASE WHEN queue_log.event = "ENTERQUEUE" THEN 1 END), 0) as sla
            ', [$slaLimit])
            ->groupBy('queues.name')
            ->get();
    }
    


}
