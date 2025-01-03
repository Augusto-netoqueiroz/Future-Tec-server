<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Definindo o período de tempo para a consulta
        $start_date = $request->input('start_date', '2024-12-01 00:00:00');
        $end_date = $request->input('end_date', '2024-12-31 23:59:59');
        
        // Consulta para TMA, TME e TMO gerais
        $generalQuery = "
            SELECT 
                AVG(billsec) AS tma, 
                AVG(duration - billsec) AS tme, 
                AVG(duration) AS tmo
            FROM cdr
            WHERE calldate BETWEEN ? AND ? 
            AND disposition = 'ANSWERED';
        ";
        $generalData = DB::select($generalQuery, [$start_date, $end_date]);
        
        // Consulta para relatório por ramal
        $ramalQuery = "
            SELECT 
                v.ramal_id AS ramal,
                COUNT(CASE WHEN c.disposition = 'ANSWERED' THEN 1 END) AS chamadas_atendidas,
                COUNT(CASE WHEN c.disposition = 'NO ANSWER' THEN 1 END) AS chamadas_recusadas,
                SUM(c.billsec) AS tempo_total_atendimento,
                AVG(c.billsec) AS tempo_medio_atendimento,
                AVG(c.duration - c.billsec) AS tempo_medio_espera
            FROM agente_ramal_vinculo AS v
            LEFT JOIN cdr AS c ON c.src = v.ramal_id
            LEFT JOIN cel ON cel.linkedid = c.uniqueid
            WHERE c.calldate BETWEEN ? AND ?
            GROUP BY v.ramal_id;
        ";
        $ramalData = DB::select($ramalQuery, [$start_date, $end_date]);
        
        // Consulta para relatório por fila
        $filaQuery = "
            SELECT 
                src AS fila,
                COUNT(CASE WHEN disposition = 'ANSWERED' THEN 1 END) AS chamadas_atendidas,
                COUNT(CASE WHEN disposition = 'NO ANSWER' THEN 1 END) AS chamadas_recusadas,
                SUM(billsec) AS tempo_total_atendimento,
                AVG(billsec) AS tempo_medio_atendimento,
                AVG(duration - billsec) AS tempo_medio_espera
            FROM cdr
            LEFT JOIN cel ON cel.linkedid = cdr.uniqueid
            WHERE calldate BETWEEN ? AND ? 
            GROUP BY src;
        ";
        $filaData = DB::select($filaQuery, [$start_date, $end_date]);

        // Retornar a view com os dados
        return view('dashboard.index', [
            'generalData' => $generalData,
            'ramalData' => $ramalData,
            'filaData' => $filaData,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }
}
