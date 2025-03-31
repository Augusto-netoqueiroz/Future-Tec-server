<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RelatorioController extends Controller
{
    public function ligacoes(Request $request)
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();
    $empresa_id = $user->empresa_id;

    // Busca os ramais e filas da empresa do usuário
    $ramais = DB::table('sippeers')
        ->where('empresa_id', $empresa_id)
        ->pluck('name')
        ->toArray();

    $filas = DB::table('queues')
        ->where('empresa_id', $empresa_id)
        ->pluck('name')
        ->toArray();

    // ⚠️ Se não há ramais e filas, não deve mostrar nada
    if (empty($ramais) && empty($filas)) {
        $chamadas = new LengthAwarePaginator([], 0, 20);
        return view('relatorios.ligacoes', compact('chamadas', 'ramais'));
    }

    $query = DB::table('cdr')
        ->select(
            'calldate',
            'src',
            'dst',
            'duration',
            'uniqueid',
            'disposition',
            'lastdata',
            'protocolo', // nova coluna adicionada
            DB::raw("IFNULL(SUBSTRING_INDEX(clid, ' ', 1), '') as Agente")
        )
        ->where(function ($q) use ($ramais, $filas) {
            $q->where(function ($inner) use ($ramais, $filas) {
                $conditions = [];

                if (!empty($ramais)) {
                    $conditions[] = ['src', $ramais];
                    $conditions[] = ['dst', $ramais];
                }

                if (!empty($filas)) {
                    $conditions[] = ['dst', $filas];
                }

                // Aplica cada condição como whereIn
                foreach ($conditions as $condition) {
                    $inner->orWhereIn($condition[0], $condition[1]);
                }
            });
        })
        ->whereRaw("disposition REGEXP '[A-Za-z]'")
        ->orderBy('calldate', 'desc');

    // Filtro de busca (pesquisa em src, dst, uniqueid e protocolo)
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('src', 'like', "%{$search}%")
              ->orWhere('dst', 'like', "%{$search}%")
              ->orWhere('uniqueid', 'like', "%{$search}%")
              ->orWhere('protocolo', 'like', "%{$search}%");
        });
    }

    // Filtro por data
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('calldate', [
            $request->input('start_date'),
            $request->input('end_date')
        ]);
    }

    // Filtro por ramal (somente se for da empresa do usuário)
    if ($request->filled('ramal')) {
        $ramal = $request->input('ramal');
        if (in_array($ramal, $ramais)) {
            $query->where(function ($q) use ($ramal) {
                $q->where('src', $ramal)
                  ->orWhere('dst', $ramal);
            });
        } else {
            // Ramal inválido — bloqueia
            $query->whereRaw('1 = 0');
        }
    }

    $chamadas = $query->paginate(20);

    // Gravações
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
        $user = auth()->user();
        $empresaId = $user->empresa_id;

        $query = DB::table('atividade')
            ->join('users', 'atividade.user_id', '=', 'users.id')
            ->select('atividade.*', 'users.name as usuario')
            ->where('users.empresa_id', $empresaId);

        if ($request->filled('acao')) {
            $query->where('acao', 'like', '%' . $request->acao . '%');
        }

        if ($request->filled('descricao')) {
            $query->where('descricao', 'like', '%' . $request->descricao . '%');
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('created_at', [$request->data_inicio, $request->data_fim]);
        }

        $atividades = $query->paginate(10);

        return view('relatorios.index', compact('atividades'));
    }
}
