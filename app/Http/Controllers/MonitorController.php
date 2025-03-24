<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;





class MonitorController extends Controller
{

    
    /**
     * Método para salvar os dados recebidos do servidor Socket.IO.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
 

     public function index()
{
    if (!Auth::check()) {
        return redirect()->route('login'); // Redireciona para a tela de login se não estiver autenticado
    }

    $user = Auth::user(); 

    Log::info('Usuário autenticado. Empresa ID: ' . $user->empresa_id);

    // Verifica se o empresa_id está na lista de IDs permitidos
    $allowedCompanies = [1, 2, 3];  
    if (!in_array($user->empresa_id, $allowedCompanies)) {
        abort(403, 'Acesso não autorizado');
    }

    $empresaId = $user->empresa_id;

    $dadosChamadas = DB::table('queue_log')
        ->join('queues', 'queue_log.queuename', '=', 'queues.name')
        ->where('queues.empresa_id', $empresaId) 
        ->whereDate('queue_log.time', today()) 
        ->selectRaw("
            COUNT(CASE WHEN queue_log.event = 'ENTERQUEUE' THEN 1 END) AS total_recebidas,
            COUNT(CASE WHEN queue_log.event = 'CONNECT' THEN 1 END) AS total_atendidas,
            COUNT(CASE WHEN queue_log.event = 'ABANDON' OR queue_log.event = 'EXITWITHTIMEOUT' THEN 1 END) AS total_perdidas
        ")
        ->first();

    return view('monitor.index', compact('dadosChamadas'));
}


     

public function getExtratoChamadas(Request $request, $tipo)
{
    $tipoEvento = match ($tipo) {
        'recebidas' => 'ENTERQUEUE',
        'atendidas' => 'CONNECT',
        default => 'ABANDON',
    };

    $limit = $request->input('limit', 10); // valor padrão: 10

    return DB::table('queue_log')
        ->join('queues', DB::raw('CONVERT(queue_log.queuename USING utf8mb4) COLLATE utf8mb4_general_ci'), '=', DB::raw('queues.name COLLATE utf8mb4_general_ci'))
        ->select(
            'queue_log.time as datetime',
            'queue_log.callid as origem',
            'queue_log.agent as destino',
            'queue_log.queuename as fila',
            DB::raw('CAST(SUBSTRING_INDEX(queue_log.data, "|", 1) AS UNSIGNED) as duracao')
        )
        ->where('queues.empresa_id', Auth::user()->empresa_id)
        ->where('queue_log.event', $tipoEvento)
        ->whereDate('queue_log.time', now()->toDateString())
        ->orderBy('queue_log.time', 'desc')
        ->limit($limit)
        ->get();
}






    public function saveSippersData(Request $request)
    {
        // Validar os dados recebidos
        $validated = $request->validate([
            'sippersData' => 'required|array',
            'sippersData.*.name' => 'required|string',
            'sippersData.*.ipaddr' => 'required|string',
            'sippersData.*.modo' => 'required|string',
            'sippersData.*.user_id' => 'required|integer',
            'sippersData.*.user_name' => 'required|string',
        ]);

        // Aqui você pode salvar os dados no banco, por exemplo:
        // foreach ($validated['sippersData'] as $sipper) {
        //     \App\Models\Sipper::create($sipper);
        // }

        // Retornar uma resposta de sucesso
        return response()->json([
            'status' => 'success',
            'message' => 'Dados recebidos e processados com sucesso!',
            'data' => $validated['sippersData']
        ]);
    }
}
