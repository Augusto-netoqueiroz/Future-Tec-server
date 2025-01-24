<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        return view('monitor.index');
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
