<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RelatorioController extends Controller
{
    public function ligacoes(Request $request)
    {
        // Inicia a consulta na tabela 'cdr' com junção da tabela 'cdr' para obter o 'clid' (Agente)
        $query = DB::table('cdr')
    ->select(
        'calldate',
        'src',
        'dst',
        'duration',
        'uniqueid',
        DB::raw("IFNULL(SUBSTRING_INDEX(clid, ' ', 1), '') as Agente")
    )
    ->orderBy('calldate', 'desc');

// Filtro de pesquisa por origem, destino ou uniqueid
if ($request->has('search') && $request->input('search') != '') {
    $search = $request->input('search');
    $query->where(function ($q) use ($search) {
        $q->where('src', 'like', "%{$search}%")
            ->orWhere('dst', 'like', "%{$search}%")
            ->orWhere('uniqueid', 'like', "%{$search}%");
    });
}

// Filtro por intervalo de data
if ($request->has('start_date') && $request->has('end_date') && $request->input('start_date') != '' && $request->input('end_date') != '') {
    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date');
    $query->whereBetween('calldate', [$start_date, $end_date]);
}

// Executa a consulta com paginação de 20 registros por página
$chamadas = $query->paginate(20);

// Caminho base onde as gravações estão armazenadas
$pathBase = '/var/www/projetolaravel/gravacoes/';

// Para cada chamada, verifica se existe o arquivo de gravação
foreach ($chamadas as $chamada) {
    $fileName = $chamada->uniqueid . '.wav'; // Nome do arquivo com base no uniqueid
    $filePath = $pathBase . $fileName;

    // Verifica se o arquivo de gravação existe
    if (file_exists($filePath)) {
        $chamada->recordingfile = $fileName;
    } else {
        $chamada->recordingfile = null;
    }
}

// Retorna a view com as chamadas, incluindo as gravações
return view('relatorios.ligacoes', compact('chamadas'));
    }
}
