<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pause;
use App\Models\User;
use App\Models\UserPauseLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PauseLog;






class PauseController extends Controller
{
    
    public function showpauses()
{
    // Obtém o usuário autenticado
    $user = Auth::user();

    // Recupera o ID e o nome da empresa do usuário autenticado
    $empresa_id = $user->empresa_id;
    $empresa_nome = $user->empresa_nome;

    // Obtém todas as pausas, exceto a de ID 6 e que pertencem à mesma empresa do usuário autenticado
    $pauses = Pause::where('empresa_id', $empresa_id) // Filtra pela empresa do usuário autenticado
                   ->where('id', '!=', 6) // Exclui a pausa de ID 6
                   ->get();
    
    // Passa as variáveis $pauses, $empresa_id e $empresa_nome para a view
    return view('pauses.pausas', compact('pauses', 'empresa_id', 'empresa_nome'));
}


    public function create()
{
    // Retorna a view para criar uma nova pausa
    return view('pauses.create');
}


public function show(Request $request)
{
    $userId = $request->input('user_id');
    
    // Buscando o último log de pausa para o usuário na tabela user_pause_logs
    $lastPause = \DB::table('user_pause_logs')
                    ->where('user_id', $userId)
                    ->orderBy('started_at', 'desc')
                    ->first(['pause_name', 'started_at']);

    if ($lastPause) {
        return response()->json($lastPause);
    } else {
        return response()->json(['message' => 'Pausa '], 404);
    }
}



public function store(Request $request)
{
    // Validação dos dados
    $request->validate([
        'name' => 'required|unique:pauses|max:255',
    ]);

    // Criação de um novo registro
    $pause = new Pause();
    $pause->name = $request->name;
    $pause->empresa_id = Auth::user()->empresa_id; // Preenche automaticamente com o empresa_id do usuário autenticado
    $pause->save();

    // Redireciona com mensagem de sucesso
    return redirect()->route('Pausas.inicio')->with('success', 'Pausa criada com sucesso!');
}


public function edit($id)
{
    // Encontra a pausa pelo ID
    $pause = Pause::findOrFail($id);

    // Retorna a view com o objeto da pausa
    return view('pauses.edit', compact('pause'));
}


public function update(Request $request, $id)
{
    // Validação dos dados
    $request->validate([
        'name' => 'required|max:255|unique:pauses,name,' . $id,
    ]);

    // Encontra o registro e atualiza
    $pause = Pause::findOrFail($id);
    $pause->name = $request->name;
    $pause->save();

    // Redireciona com mensagem de sucesso
    return redirect()->route('Pausas.inicio')->with('success', 'Pausa atualizada com sucesso!');
}


    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login'); // Redireciona para a tela de login se não estiver autenticado
        }
        
        try {
            $pauses = Pause::all();
            return response()->json($pauses);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar pausas'], 500);
        }
    }

    public function startPause(Request $request)
{
    $request->validate([
        'pause_id' => 'required|exists:pauses,id',
        'user_id' => 'required|exists:users,id',
    ]);

    try {
        $user = User::findOrFail($request->user_id);
        $pause = Pause::findOrFail($request->pause_id);

        // Encerra o log ativo atual
        if ($user->current_pause_log_id) {
            $currentLog = UserPauseLog::findOrFail($user->current_pause_log_id);
            $currentLog->update(['end_at' => now()]);
        }

        // Cria um novo log para a pausa
        $log = $user->pauseLogs()->create([
            'pause_id' => $pause->id,
            'started_at' => now(),
        ]);

        $user->update([
            'pause' => $pause->name,
            'current_pause_log_id' => $log->id,
        ]);

        return response()->json(['message' => 'Pausa iniciada com sucesso']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao iniciar pausa'], 500);
    }
}

public function endPause(Request $request)
{
    $request->validate(['user_id' => 'required|exists:users,id']);

    try {
        $user = User::findOrFail($request->user_id);

        if ($user->current_pause_log_id) {
            $currentLog = UserPauseLog::findOrFail($user->current_pause_log_id);
            $currentLog->update(['end_at' => now()]);
        }

        $user->update([
            'pause' => null,
            'current_pause_log_id' => null,
        ]);

        return response()->json(['message' => 'Pausa encerrada com sucesso']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao encerrar pausa'], 500);
    }
}

    
   



public function getUserPauses($userId)
{
    try {
        Log::info('Buscando pausas para o usuário', ['user_id' => $userId]);

        // Buscando as pausas do usuário no banco de dados
        $pauses = UserPauseLog::where('user_id', $userId)
                              ->orderBy('start_time', 'desc')
                              ->get();

        Log::info('Pausas encontradas', ['pauses' => $pauses]);

        return response()->json($pauses);
    } catch (\Exception $e) {
        Log::error('Erro ao buscar pausas para o usuário', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);

        return response()->json(['message' => 'Erro ao buscar pausas'], 500);
    }
}



public function getLastPause(Request $request)
{
    $userId = $request->input('user_id');
    
    // Buscando o último log de pausa para o usuário na tabela user_pause_logs
    $lastPause = \DB::table('user_pause_logs')
                    ->where('user_id', $userId)
                    ->orderBy('started_at', 'desc')
                    ->first(['pause_name', 'started_at']);

    if ($lastPause) {
        return response()->json($lastPause);
    } else {
        return response()->json(['message' => 'Pausa '], 404);
    }
}





public function relatorio()
{
    $user = auth()->user();

    // Busca apenas os usuários da mesma empresa do usuário autenticado
    $usuarios = User::where('empresa_id', $user->empresa_id)->get();

    return view('pauses.relatorio', compact('usuarios'));
}

public function filtrarRelatorio(Request $request)
{
    $user = auth()->user();

    $query = DB::table('user_pause_logs')
        ->join('users', 'users.id', '=', 'user_pause_logs.user_id')
        ->where('users.empresa_id', $user->empresa_id) // Filtra apenas usuários da mesma empresa
        ->select(
            'user_pause_logs.*',
            'users.name as user_name',
            DB::raw('TIMESTAMPDIFF(SECOND, started_at, end_at) as duration_seconds')
        )
        ->orderBy('user_pause_logs.started_at', 'desc'); // Ordenação DESC

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('started_at', [$request->start_date, $request->end_date]);
    }

    if ($request->filled('user_id')) {
        $query->where('user_pause_logs.user_id', $request->user_id);
    }

    $logs = $query->paginate(10); // Adicionando paginação Laravel

    return response()->json([
        'logs' => $logs->items(),
        'pagination' => (string) $logs->appends(request()->query())->links('pagination::bootstrap-4'),
    ]);
}





}

