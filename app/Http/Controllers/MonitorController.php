<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

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
         // Buscar os dados agregados da tabela queue_log apenas para o dia atual
         $dadosChamadas = DB::table('queue_log')
             ->whereDate('time', today())
             ->selectRaw("COUNT(CASE WHEN event = 'ENTERQUEUE' THEN 1 END) AS total_recebidas,
                          COUNT(CASE WHEN event = 'CONNECT' THEN 1 END) AS total_atendidas,
                          COUNT(CASE WHEN event = 'ABANDON' OR event = 'EXITWITHTIMEOUT' THEN 1 END) AS total_perdidas")
             ->first();
     
         return view('monitor.index', compact('dadosChamadas'));
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
