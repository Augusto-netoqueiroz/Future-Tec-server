<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RamalController extends Controller
{
    public function consultarEstado()
    {
        $ramais = DB::table('sippeers')
            ->leftJoin('users', 'sippeers.user_id', '=', 'users.id')
            ->select('sippeers.name as ramal', 'sippeers.ipaddr', 'users.name as atendente')
            ->get();

        $resultado = $ramais->map(function ($ramal) {
            return [
                'ramal' => $ramal->ramal,
                'estado' => strlen($ramal->ipaddr) > 6 ? 1 : 0,
                'atendente' => $ramal->atendente,
            ];
        });

        return response()->json($resultado);
    }

    public function index()
{

    if (!Auth::check()) {
        return redirect()->route('login'); // Redireciona para a tela de login se não estiver autenticado
    }
    
    $empresa_id = Auth::user()->empresa_id;
    $empresa_nome = Auth::user()->empresa_nome;

    $ramais = DB::table('sippeers')
        ->where('modo', 'ramal')
        ->where('sippeers.empresa_id', $empresa_id) // Filtra pelos ramais da empresa
        ->leftJoin('users', 'sippeers.user_id', '=', 'users.id')
        ->select('sippeers.id', 'sippeers.name as ramal', 'sippeers.ipaddr', 'sippeers.context', 'users.name as atendente')
        ->get();

    // Define o estado do ramal (Online/Offline)
    $ramais = $ramais->map(function ($ramal) {
        $ramal->estado = strlen($ramal->ipaddr) > 5 ? 'Online' : 'Offline';
        return $ramal;
    });

    return view('ramais.index', compact('ramais'));
}


    public function create()
    {
        $users = DB::table('users')->get();
        return view('ramais.create', compact('users'));
    }

    public function edit($id)
{
    $ramal = DB::table('sippeers')->where('id', $id)->first();

    if (!$ramal) {
        return redirect()->route('ramais.index')->with('error', 'Ramal não encontrado.');
    }

    return view('ramais.edit', compact('ramal'));
} 

public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:sippeers,name,' . $id,
        'secret' => 'required|string|max:255',
        'context' => 'required|string|max:255',
    ]);

    // Obtém os dados atuais do ramal antes da atualização
    $ramal = DB::table('sippeers')->where('id', $id)->first();

    if (!$ramal) {
        return redirect()->route('ramais.index')->with('error', 'Ramal não encontrado.');
    }

    // Monta a descrição das mudanças
    $descricao = "Usuário " . Auth::user()->name . " atualizou o ramal #$id";
    
    if ($ramal->name !== $request->name) {
        $descricao .= " (Nome: {$ramal->name} → {$request->name})";
    }
    if ($ramal->secret !== $request->secret) {
        $descricao .= " (Senha alterada)";
    }
    if ($ramal->context !== $request->context) {
        $descricao .= " (Contexto: {$ramal->context} → {$request->context})";
    }

    // Atualiza os dados do ramal
    DB::table('sippeers')->where('id', $id)->update([
        'name' => $request->name,
        'secret' => $request->secret,
        'context' => $request->context,
       
    ]);

    // Registrar a atividade no banco
    DB::table('atividade')->insert([
        'user_id'   => Auth::id(),
        'acao'      => 'Atualização de ramal',
        'descricao' => $descricao,
        'ip'        => request()->ip(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('ramais.index')->with('success', 'Ramal atualizado com sucesso!');
}



public function store(Request $request)
{
    // Obtém o ID da empresa do usuário autenticado
    $empresa_id = Auth::user()->empresa_id;

    // Validação dos campos
    $request->validate([
        'ramal' => 'required|string|max:255|unique:sippeers,name',
        'senha' => 'required|string|max:255',
        'context' => 'required|string|max:255',
    ]);

    // Inserção do novo ramal na empresa do usuário
    $ramal_id = DB::table('sippeers')->insertGetId([
        'name' => $request->ramal,
        'secret' => $request->senha,
        'host' => 'dynamic',
        'context' => $request->context,
        'ipaddr' => '',
        'modo' => 'ramal',
        'empresa_id' => $empresa_id, // Adiciona o empresa_id corretamente
    ]);

    // Registrar a atividade no banco
    DB::table('atividade')->insert([
        'user_id'   => Auth::id(),
        'acao'      => 'Criação de ramal',
        'descricao' => "Usuário " . Auth::user()->name . " criou o ramal #$ramal_id ($request->ramal)",
        'ip'        => request()->ip(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('ramais.index')->with('success', 'Ramal criado com sucesso!');
}



    public function destroy($id)
    {
    // Buscar o nome do ramal antes de excluir
    $ramal = DB::table('sippeers')->where('id', $id)->first();

    if (!$ramal) {
        return redirect()->route('ramais.index')->with('error', 'Ramal não encontrado.');
    }

    // Excluir o ramal
    DB::table('sippeers')->where('id', $id)->delete();

    // Registrar a atividade no banco
    DB::table('atividade')->insert([
        'user_id'   => Auth::id(),
        'acao'      => 'Exclusão de ramal',
        'descricao' => "Usuário " . Auth::user()->name . " deletou o ramal #$id ($ramal->name)",
        'ip'        => request()->ip(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('ramais.index')->with('success', 'Ramal excluído com sucesso!');
}


   

    public function listarRamais()
    {
        $ramais = DB::table('sippeers')
            ->where('modo', 'ramal')
            ->leftJoin('users', 'sippeers.user_id', '=', 'users.id')
            ->select('sippeers.id', 'sippeers.name as ramal', 'sippeers.ipaddr', 'users.name as atendente')
            ->get();
    
        Log::info('Ramais filtrados:', $ramais->toArray());
    
        $ramais = $ramais->map(function ($ramal) {
            $ramal->estado = strlen($ramal->ipaddr) > 5 ? 'Online' : 'Offline';
            $ramal->ipaddr = $ramal->ipaddr ?: 'Sem IP';
            return $ramal;
        });
    
        return view('ramais.index', compact('ramais'));
    }
    


    // Adicionando funcionalidades para Troncos
    public function listarTroncos()
    {
        $empresa_id = Auth::user()->empresa_id; // Obtém o empresa_id do usuário logado
    
        $troncos = DB::table('sippeers')
            ->where('type', 'friend') // Filtra apenas troncos
            ->where('empresa_id', $empresa_id) // Filtra apenas os troncos da empresa do usuário
            ->select('id', 'name as tronco', 'host', 'context', 'ipaddr')
            ->get();
    
        $troncos = $troncos->map(function ($tronco) {
            $tronco->estado = strlen($tronco->ipaddr) > 5 ? 'Online' : 'Offline';
            $tronco->ipaddr = $tronco->ipaddr ?: 'Sem IP';
            return $tronco;
        });
    
        return view('troncos.index', compact('troncos'));
    }
    

    public function criarTronco()
    {
        return view('troncos.create');
    }

    public function salvarTronco(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:sippeers,name',
            'senha' => 'required|string|max:255',
            'context' => 'required|string|max:255',
            'host' => 'nullable|string|max:255',  
        ]);
    
        // Inserção do tronco
        $id = DB::table('sippeers')->insertGetId([
            'name' => $request->nome,
            'secret' => $request->senha,
            'host' => $request->host ?? '',
            'context' => $request->context,
            'type' => 'friend',
            'qualify' => 'yes',
            'ipaddr' => '',
            'modo' => 'tronco', 
            'empresa_id' => Auth::user()->empresa_id,
        ]);
    
        // Registrar atividade
        DB::table('atividade')->insert([
            'user_id'   => Auth::id(),
            'acao'      => 'Criação de tronco',
            'descricao' => "Usuário " . Auth::user()->name . " criou o tronco #$id ({$request->nome})",
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return redirect()->route('troncos.index')->with('success', 'Tronco criado com sucesso!');
    }
    
    public function editarTronco($id)
    {
        $tronco = DB::table('sippeers')->where('id', $id)->first();
    
        if (!$tronco) {
            return redirect()->route('troncos.index')->with('error', 'Tronco não encontrado.');
        }
    
        return view('troncos.edit', compact('tronco'));
    }
    
    public function atualizarTronco(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:sippeers,name,' . $id,
            'senha' => 'required|string|max:255',
            'context' => 'required|string|max:255',
            'host' => 'required|string|max:255',
        ]);
    
        // Obtém os dados atuais do tronco antes da atualização
        $tronco = DB::table('sippeers')->where('id', $id)->first();
    
        if (!$tronco) {
            return redirect()->route('troncos.index')->with('error', 'Tronco não encontrado.');
        }
    
        // Monta a descrição das mudanças
        $descricao = "Usuário " . Auth::user()->name . " atualizou o tronco #$id";
    
        if ($tronco->name !== $request->nome) {
            $descricao .= " (Nome: {$tronco->name} → {$request->nome})";
        }
        if ($tronco->secret !== $request->senha) {
            $descricao .= " (Senha alterada)";
        }
        if ($tronco->context !== $request->context) {
            $descricao .= " (Contexto: {$tronco->context} → {$request->context})";
        }
        if ($tronco->host !== $request->host) {
            $descricao .= " (Host: {$tronco->host} → {$request->host})";
        }
    
        // Atualiza os dados do tronco
        DB::table('sippeers')->where('id', $id)->update([
            'name' => $request->nome,
            'secret' => $request->senha,
            'host' => $request->host,
            'context' => $request->context,
            'updated_at' => now(),
        ]);
    
        // Registrar a atividade no banco
        DB::table('atividade')->insert([
            'user_id'   => Auth::id(),
            'acao'      => 'Atualização de tronco',
            'descricao' => $descricao,
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return redirect()->route('troncos.index')->with('success', 'Tronco atualizado com sucesso!');
    }
    
    public function destroytronco($id)
    {
        // Obtém os dados do tronco antes de excluir
        $tronco = DB::table('sippeers')->where('id', $id)->first();
    
        if (!$tronco) {
            return redirect()->route('troncos.index')->with('error', 'Tronco não encontrado.');
        }
    
        DB::table('sippeers')->where('id', $id)->delete();
    
        // Registrar a atividade no banco
        DB::table('atividade')->insert([
            'user_id'   => Auth::id(),
            'acao'      => 'Exclusão de tronco',
            'descricao' => "Usuário " . Auth::user()->name . " deletou o tronco #$id ({$tronco->name})",
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return redirect()->route('troncos.index')->with('success', 'Tronco excluído com sucesso!');
    }
    


}
