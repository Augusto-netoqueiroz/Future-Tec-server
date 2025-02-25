<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RelatorioController extends Controller
{
    public function ligacoes(Request $request)
{
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
}
