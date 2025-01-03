<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilaController extends Controller
{
    // Exibe a página principal de filas
    public function index()
    {
        $filas = DB::table('queues')->get(); // Alterado de 'queue' para 'queues'
        return view('filas.filas', compact('filas'));
    }

    // Exibe o formulário de criação de fila
    public function create()
    {
        return view('filas.create');
    }

    // Salva uma nova fila no banco de dados
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:queues,name',
        'strategy' => 'required|string|max:255',
        'timeout' => 'required|integer',
        'musiconhold' => 'nullable|string|max:128',
        'announce_frequency' => 'nullable|integer',
        'servicelevel' => 'nullable|integer',
        'wrapuptime' => 'nullable|integer',
        'joinempty' => 'nullable|string|max:128',
        'leavewhenempty' => 'nullable|string|max:128',
        'ringinuse' => 'nullable|boolean',
    ]);

    DB::table('queues')->insert([
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

    return redirect()->route('filas.index')->with('success', 'Fila criada com sucesso!');
}

    // Exibe o formulário de edição de fila
    public function edit($id)
{
    // Busca a fila pelo ID
    $fila = DB::table('queues')->where('id', $id)->first();

    // Verifica se a fila existe
    if (!$fila) {
        return redirect()->route('filas.index')->with('error', 'Fila não encontrada.');
    }

    // Retorna a view de edição com os dados da fila
    return view('filas.edit', compact('fila'));
}


    // Atualiza uma fila existente
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:queues,name,' . $id, // Alterado para 'queues'
            'strategy' => 'required|string|max:255',
            'timeout' => 'required|integer',
        ]);

        DB::table('queues')->where('id', $id)->update([ // Alterado para 'queues'
            'name' => $request->name,
            'strategy' => $request->strategy,
            'timeout' => $request->timeout,
        ]);

        return redirect()->route('filas.index')->with('success', 'Fila atualizada com sucesso!');
    }

    // Deleta uma fila
    public function destroy($id)
    {
        DB::table('queues')->where('id', $id)->delete(); // Alterado para 'queues'
        return redirect()->route('filas.index')->with('success', 'Fila deletada com sucesso!');
    }

    // Exibe a tela de gerenciamento de membros da fila
    public function manageMembers($id)
    {
        $fila = DB::table('queues')->where('id', $id)->first(); // Alterado para 'queues'
        $members = DB::table('queue_members')->where('queue_id', $id)->get();
        $users = DB::table('users')->get(); // Supondo que os membros são usuários do sistema

        return view('filas.manage', compact('fila', 'members', 'users'));
    }

    // Associa um membro à fila
    public function associateMember(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        DB::table('queue_members')->insert([
            'queue_id' => $id,
            'user_id' => $request->user_id,
        ]);

        return redirect()->route('filas.manage', $id)->with('success', 'Membro associado à fila com sucesso!');
    }

    // Desassocia um membro da fila
    public function removeMember($queueId, $userId)
    {
        DB::table('queue_members')
            ->where('queue_id', $queueId)
            ->where('user_id', $userId)
            ->delete();

        return redirect()->route('filas.manage', $queueId)->with('success', 'Membro removido da fila com sucesso!');
    }
}
