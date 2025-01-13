<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
{
    $start_date = $request->get('start_date', now()->startOfMonth()->toDateTimeString());
    $end_date = $request->get('end_date', now()->endOfMonth()->toDateTimeString());

    // Consultas ao banco
    $generalData = DB::select("
        SELECT 
            AVG(duration) AS tma, 
            AVG(GREATEST(duration - CAST(SUBSTRING_INDEX(data, ',', 1) AS SIGNED), 0)) AS tme, 
            AVG(duration + CAST(SUBSTRING_INDEX(data, ',', 1) AS SIGNED)) AS tmo
        FROM queue_log
        WHERE time BETWEEN ? AND ?
        AND event IN ('COMPLETECALLER', 'COMPLETEAGENT')
    ", [$start_date, $end_date]);

    $ramalData = collect(DB::select("
        SELECT 
            agent AS ramal,
            COUNT(CASE WHEN event = 'COMPLETECALLER' OR event = 'COMPLETEAGENT' THEN 1 END) AS chamadas_atendidas,
            COUNT(CASE WHEN event = 'ABANDON' THEN 1 END) AS chamadas_recusadas,
            SUM(duration) AS tempo_total_atendimento,
            AVG(duration) AS tempo_medio_atendimento,
            AVG(GREATEST(duration - CAST(SUBSTRING_INDEX(data, ',', 1) AS SIGNED), 0)) AS tempo_medio_espera
        FROM queue_log
        WHERE time BETWEEN ? AND ?
        GROUP BY agent
    ", [$start_date, $end_date]));

    $filaData = collect(DB::select("
        SELECT 
            queuename AS fila,
            COUNT(CASE WHEN event = 'COMPLETECALLER' OR event = 'COMPLETEAGENT' THEN 1 END) AS chamadas_atendidas,
            COUNT(CASE WHEN event = 'ABANDON' THEN 1 END) AS chamadas_recusadas,
            SUM(duration) AS tempo_total_atendimento,
            AVG(duration) AS tempo_medio_atendimento,
            AVG(GREATEST(duration - CAST(SUBSTRING_INDEX(data, ',', 1) AS SIGNED), 0)) AS tempo_medio_espera
        FROM queue_log
        WHERE time BETWEEN ? AND ?
        GROUP BY queuename
    ", [$start_date, $end_date]));

    return view('dashboard.index', compact('generalData', 'ramalData', 'filaData', 'start_date', 'end_date'));
}

}
