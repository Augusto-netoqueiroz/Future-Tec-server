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

        if (Auth::check()) {
            Log::info('Usuário autenticado. Empresa ID: ' . Auth::user()->empresa_id);
        } else {
            Log::warning('Usuário não autenticado.');
        }
        
        // Verifica se o empresa_id está na lista de IDs permitidos
        $allowedCompanies = [1, 2, 3];  // IDs das empresas permitidas
        if (Auth::check() && !in_array(Auth::user()->empresa_id, $allowedCompanies)) {
            abort(403, 'Acesso não autorizado');
        }
        
        
         // Buscar os dados agregados da tabela queue_log apenas para o dia atual
         $dadosChamadas = DB::table('queue_log')
             ->whereDate('time', today())
             ->selectRaw("COUNT(CASE WHEN event = 'ENTERQUEUE' THEN 1 END) AS total_recebidas,
                          COUNT(CASE WHEN event = 'CONNECT' THEN 1 END) AS total_atendidas,
                          COUNT(CASE WHEN event = 'ABANDON' OR event = 'EXITWITHTIMEOUT' THEN 1 END) AS total_perdidas")
             ->first();
     
         return view('monitor.index', compact('dadosChamadas'));
     }
     

     public function getExtratoChamadas($tipo)
     {
         $query = DB::table('queue_log')
             ->select(
                 'time as datetime', 
                 'callid as origem',  // Substituindo 'caller' por 'callid' (Ajuste conforme necessário)
                 'agent as destino', 
                 'queuename as fila',
                 'duration as duracao' // Substituindo 'data3' por 'duration'
             )
             ->where('event', $tipo == 'recebidas' ? 'ENTERQUEUE' : ($tipo == 'atendidas' ? 'CONNECT' : 'ABANDON'))
             ->orderBy('time', 'desc')
             ->limit(10)
             ->get();
     
         return response()->json($query);
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
