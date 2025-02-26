<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RelatorioController extends Controller
{
    public function ligacoes(Request $request)
{
    if (!auth()->check()) {
        return redirect()->route('login'); // Certifique-se de que a rota 'login' está correta
    }

    $user = auth()->user();
    $empresa_id = $user->empresa_id;

    $ramais = DB::table('sippeers')
        ->where('empresa_id', $empresa_id)
        ->pluck('name')
        ->toArray();

    $filas = DB::table('queues')
        ->where('empresa_id', $empresa_id)
        ->pluck('name')
        ->toArray();

    $query = DB::table('cdr')
        ->select(
            'calldate',
            'src',
            'dst',
            'duration',
            'uniqueid',
            'disposition',
            'lastdata',
            DB::raw("IFNULL(SUBSTRING_INDEX(clid, ' ', 1), '') as Agente")
        )
        ->where(function ($q) use ($ramais, $filas) {
            if (!empty($ramais)) {
                $q->whereIn('src', $ramais)
                  ->orWhereIn('dst', $ramais);
            }
            if (!empty($filas)) {
                $q->orWhereIn('dst', $filas);
            }
        })
        ->whereRaw("disposition REGEXP '[A-Za-z]'")
        ->orderBy('calldate', 'desc');

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('src', 'like', "%{$search}%")
              ->orWhere('dst', 'like', "%{$search}%")
              ->orWhere('uniqueid', 'like', "%{$search}%");
        });
    }

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('calldate', [
            $request->input('start_date'),
            $request->input('end_date')
        ]);
    }

    if ($request->filled('ramal')) {
        $query->where(function ($q) use ($request) {
            $q->where('src', $request->input('ramal'))
              ->orWhere('dst', $request->input('ramal'));
        });
    }

    $chamadas = $query->paginate(20);

    $pathBase = '/var/www/projetolaravel/gravacoes/';

    foreach ($chamadas as $chamada) {
        $filePath = $pathBase . $chamada->uniqueid . '.wav';
        $chamada->recordingfile = file_exists($filePath) ? $chamada->uniqueid . '.wav' : null;
    }

    if ($request->ajax()) {
        return response()->json(['chamadas' => $chamadas]);
    }

    return view('relatorios.ligacoes', compact('chamadas', 'ramais'));
}


public function index(Request $request)
{
    $user = auth()->user(); // Pegando o usuário autenticado
    $empresaId = $user->empresa_id; // Pegando o empresa_id do usuário

    // Inicia a query filtrando pela empresa do usuário
    $query = DB::table('atividade')
        ->join('users', 'atividade.user_id', '=', 'users.id')
        ->select('atividade.*', 'users.name as usuario')
        ->where('users.empresa_id', $empresaId);

    // Filtros opcionais
    if ($request->filled('acao')) {
        $query->where('acao', 'like', '%' . $request->acao . '%');
    }

    if ($request->filled('descricao')) {
        $query->where('descricao', 'like', '%' . $request->descricao . '%');
    }

    if ($request->filled('data_inicio') && $request->filled('data_fim')) {
        $query->whereBetween('created_at', [$request->data_inicio, $request->data_fim]);
    }

    // Paginação
    $atividades = $query->paginate(10);

    return view('relatorios.index', compact('atividades'));
}





}
