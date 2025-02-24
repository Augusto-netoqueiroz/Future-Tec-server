<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FilaController extends Controller
{
    // Exibe a página principal de filas
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Usuário não autenticado.');
        }
    
        $empresa_id = Auth::user()->empresa_id;
        $empresa_nome = Auth::user()->empresa_nome;
    
        $filas = DB::table('queues')
            ->where('empresa_id', $empresa_id)
            ->get();
    
        return view('filas.filas', compact('filas'));
    }

    // Exibe o formulário de criação de fila
    public function create()
    {
        return view('filas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'strategy' => 'required|string|max:255',
            'timeout' => 'required|integer',
            'musiconhold' => 'nullable|string|max:255',
            'announce_frequency' => 'nullable|integer',
            'servicelevel' => 'nullable|integer',
            'wrapuptime' => 'nullable|integer',
            'joinempty' => 'nullable|string|in:yes,no',
            'leavewhenempty' => 'nullable|string|in:yes,no',
            'ringinuse' => 'nullable|boolean',
        ]);
    
        // Buscar o último ID cadastrado e incrementar
        $lastId = DB::table('queues')->max('id');
        $newId = $lastId ? $lastId + 1 : 1; // Se não houver registros, inicia em 1
    
        DB::table('queues')->insert([
            'id' => $newId,
            'name' => $request->name,
            'strategy' => $request->strategy,
            'timeout' => $request->timeout,
            'musiconhold' => $request->musiconhold,
            'announce_frequency' => $request->announce_frequency,
            'servicelevel' => $request->servicelevel,
            'wrapuptime' => $request->wrapuptime,
            'joinempty' => $request->joinempty,
            'leavewhenempty' => $request->leavewhenempty,
            'ringinuse' => $request->ringinuse,
            'empresa_id' => Auth::user()->empresa_id,
        ]);
    
        // **Registrar a atividade no banco**
        DB::table('atividade')->insert([
            'user_id'   => Auth::id(),
            'acao'      => 'Criação de Fila',
            'descricao' => "Usuário " . Auth::user()->name . " criou a fila $newId - $request->name",
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return redirect()->route('filas.index')->with('success', 'Fila criada com sucesso!');
    }
    


// Exibe o formulário de edição de uma fila
public function edit($id)
{
    $fila = DB::table('queues')->where('id', $id)->first();

    if (!$fila) {
        return redirect()->route('filas.index')->with('error', 'Fila não encontrada.');
    }

    return view('filas.edit', compact('fila'));
}

// Atualiza uma fila no banco de dados
public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'strategy' => 'required|string|max:255',
        'timeout' => 'required|integer',
        'musiconhold' => 'nullable|string|max:255',
        'announce_frequency' => 'nullable|integer',
        'servicelevel' => 'nullable|integer',
        'wrapuptime' => 'nullable|integer',
        'joinempty' => 'nullable|string|in:yes,no',
        'leavewhenempty' => 'nullable|string|in:yes,no',
        'ringinuse' => 'nullable|boolean',
    ]);

    $fila = DB::table('queues')->where('id', $id)->first();

    if (!$fila) {
        return redirect()->route('filas.index')->with('error', 'Fila não encontrada.');
    }

    // Criar um array para armazenar as mudanças
    $mudancas = [];

    // Verifica quais campos foram alterados
    $campos = [
        'name', 'strategy', 'timeout', 'musiconhold', 'announce_frequency',
        'servicelevel', 'wrapuptime', 'joinempty', 'leavewhenempty', 'ringinuse'
    ];

    foreach ($campos as $campo) {
        if ($fila->$campo != $request->$campo) {
            $mudancas[] = ucfirst($campo) . " de '{$fila->$campo}' para '{$request->$campo}'";
        }
    }

    // Atualizar os dados da fila
    DB::table('queues')
        ->where('id', $id)
        ->update([
            'name' => $request->name,
            'strategy' => $request->strategy,
            'timeout' => $request->timeout,
            'musiconhold' => $request->musiconhold,
            'announce_frequency' => $request->announce_frequency,
            'servicelevel' => $request->servicelevel,
            'wrapuptime' => $request->wrapuptime,
            'joinempty' => $request->joinempty,
            'leavewhenempty' => $request->leavewhenempty,
            'ringinuse' => $request->ringinuse,
            
        ]);

    // Criar a descrição com as mudanças
    $descricao = "Usuário " . Auth::user()->name . " atualizou a fila $id - $request->name.";
    if (!empty($mudancas)) {
        $descricao .= " Alterações: " . implode(', ', $mudancas) . ".";
    }

    // Registrar a atividade no banco
    DB::table('atividade')->insert([
        'user_id'   => Auth::id(),
        'acao'      => 'Atualização de Fila',
        'descricao' => $descricao,
        'ip'        => request()->ip(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('filas.index')->with('success', 'Fila atualizada com sucesso!');
}


   // Exibe a tela de gerenciamento de membros da fila
   public function manageMembers($id)
   {
       $fila = DB::table('queues')->where('id', $id)->first();
   
       if (!$fila) {
           return redirect()->route('filas.index')->with('error', 'Fila não encontrada.');
       }
   
       // Recupera os membros da fila
       $members = DB::table('queue_members')
           ->leftJoin('users', DB::raw('CONVERT(queue_members.member_name USING utf8mb4)'), '=', DB::raw('CONVERT(users.name USING utf8mb4)'))
           ->where('queue_members.queue_name', $fila->name)
           ->select('queue_members.*', 'users.name as membername')
           ->get();
   
       // Recupera usuários disponíveis para associação (que não estão na fila) e pertencem à mesma empresa do usuário logado
       $users = DB::table('users')
           ->whereNotIn('id', function ($query) use ($fila) {
               $query->select('user_id')
                   ->from('queue_members')
                   ->where('queue_name', $fila->name);
           })
           ->where('empresa_id', Auth::user()->empresa_id) // Filtra apenas usuários da mesma empresa
           ->get();
   
       return view('filas.manage', compact('fila', 'members', 'users'));
   }
   

// Associa um membro à fila
public function associateMember(Request $request, $id)
{
    $request->validate([
        'user_ids' => 'required|array',
        'penalty' => 'required|integer',
        'paused' => 'required|boolean',
    ]);

    $queue = DB::table('queues')->where('id', $id)->first();

    if (!$queue) {
        return redirect()->route('filas.index')->with('error', 'Fila não encontrada.');
    }

    $adicionados = [];

    foreach ($request->user_ids as $userId) {
        $user = DB::table('users')->where('id', $userId)->select('name', 'empresa_id')->first();

        if (!$user) {
            continue;
        }

        DB::table('queue_members')->insert([
            'queue_name' => $queue->name,
            'member_name' => $user->name,
            'interface' => '',
            'state_interface' => null,
            'penalty' => $request->penalty,
            'paused' => $request->paused,
            'queue_id' => $id,
            'user_id' => $userId,
            'empresa_id' => $user->empresa_id,
            'uniqueid' => uniqid('', true),
        ]);

        $adicionados[] = $user->name;
    }

    if (!empty($adicionados)) {
        DB::table('atividade')->insert([
            'user_id'   => Auth::id(),
            'acao'      => 'Associação de Membro',
            'descricao' => "Usuário " . Auth::user()->name . " associou " . implode(', ', $adicionados) . " à fila $queue->name.",
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return redirect()->route('filas.manage', $id)->with('success', 'Membro(s) associado(s) à fila com sucesso!');
}

// Remove um membro da fila
public function removeMember($filaId, $userId)
{
    $membro = DB::table('queue_members')->where('queue_id', $filaId)->where('user_id', $userId)->first();

    if ($membro) {
        DB::table('queue_members')->where('queue_id', $filaId)->where('user_id', $userId)->delete();

        DB::table('atividade')->insert([
            'user_id'   => Auth::id(),
            'acao'      => 'Remoção de Membro',
            'descricao' => "Usuário " . Auth::user()->name . " removeu $membro->member_name da fila ID: $filaId.",
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return redirect()->route('filas.manage', $filaId)->with('success', 'Membro removido com sucesso!');
}

// Atualiza o estado de um membro na fila
public function updateMemberState(Request $request, $queueId, $uniqueid)
{
    $request->validate([
        'paused' => 'required|boolean',
        'penalty' => 'required|integer',
    ]);

    $member = DB::table('queue_members')
        ->where('queue_id', $queueId)
        ->where('uniqueid', $uniqueid)
        ->first();

    if (!$member) {
        return redirect()->route('filas.manage', $queueId)->with('error', 'Membro não encontrado.');
    }

    $mudancas = [];

    if ($member->paused != $request->paused) {
        $mudancas[] = "Paused de '{$member->paused}' para '{$request->paused}'";
    }

    if ($member->penalty != $request->penalty) {
        $mudancas[] = "Penalty de '{$member->penalty}' para '{$request->penalty}'";
    }

    DB::table('queue_members')
        ->where('queue_id', $queueId)
        ->where('uniqueid', $uniqueid)
        ->update([
            'paused' => $request->paused,
            'penalty' => $request->penalty,
        ]);

    if (!empty($mudancas)) {
        DB::table('atividade')->insert([
            'user_id'   => Auth::id(),
            'acao'      => 'Atualização de Estado do Membro',
            'descricao' => "Usuário " . Auth::user()->name . " atualizou o estado do membro $member->member_name na fila ID: $queueId. " . implode(', ', $mudancas),
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return redirect()->route('filas.manage', $queueId)->with('success', 'Estado do membro atualizado com sucesso!');
}

}
