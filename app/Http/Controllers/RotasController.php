<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RotasController extends Controller
{
    // Exibe todas as rotas
    public function index()
{

    $empresa_id = Auth::user()->empresa_id;
    $empresa_nome = Auth::user()->empresa_nome;

    $rotas = DB::table('extensions')
    ->where('extensions.empresa_id', $empresa_id)
    ->get(); // Recupera todas as rotas do banco de dados
    return view('rotas.index', compact('rotas')); // Passa a variável $rotas para a view
}

    // Exibe o formulário para criar uma nova rota
    public function create()
    {
        return view('rotas.create');
    }

    // Armazena uma nova rota no banco de dados
    public function store(Request $request)
    {
        // Validação dos dados recebidos
        $validated = $request->validate([
            'contexto_discagem' => 'required|string',
            'discagem' => 'required|string',
            'destino' => 'required|string',
            'tipo_discagem' => 'required|string|in:ramal,fila', // Adiciona o tipo de discagem
        ]);
    
        // Preparação dos dados
        $traco = '_';
        $discagemCompleta = $traco . $validated['discagem'];
    
        // Define app e appdata com base no tipo de discagem
        $app = $validated['tipo_discagem'] === 'ramal' ? 'Dial' : 'Queue';
        $appdata = $validated['tipo_discagem'] === 'ramal'
            ? "SIP/{$validated['destino']},120"
            : $validated['destino'];
    
        // Inserção no banco de dados
        DB::table('extensions')->insert([
            'context' => $validated['contexto_discagem'],
            'exten' => $discagemCompleta,
            'priority' => 1,
            'app' => $app,
            'appdata' => $appdata,
            'tipo_discagem' => $validated['tipo_discagem'],
            'empresa_id' => Auth::user()->empresa_id, // Adicionando o empresa_id do usuário logado
        ]);
    
        return redirect()->route('rotas.index')->with('status', 'Rota criada com sucesso!');
    }
    
    // Atualiza uma rota existente no banco de dados
    public function update(Request $request, $id)
    {
        // Validação dos dados recebidos
        $validated = $request->validate([
            'contexto_discagem' => 'required|string',
            'discagem' => 'required|string',
            'destino' => 'required|string',
            'tipo_discagem' => 'required|string|in:ramal,fila',
        ]);

        // Preparação dos dados
        $traco = '_';
        $discagemCompleta = $traco . $validated['discagem'];

        $app = $validated['tipo_discagem'] === 'ramal' ? 'Dial' : 'Queue';
        $appdata = $validated['tipo_discagem'] === 'ramal'
            ? "SIP/{$validated['destino']},120"
            : $validated['destino'];

        // Atualização no banco de dados
        DB::table('extensions')->where('id', $id)->update([
            'context' => $validated['contexto_discagem'],
            'exten' => $discagemCompleta,
            'priority' => 1,
            'app' => $app,
            'appdata' => $appdata,
            'tipo_discagem' => $validated['tipo_discagem'],
        ]);

        return redirect()->route('rotas.index')->with('status', 'Rota atualizada com sucesso!');
    }

    // Remove uma rota do banco de dados
    public function destroy($id)
    {
        DB::table('extensions')->where('id', $id)->delete();

        return redirect()->route('rotas.index')->with('status', 'Rota deletada com sucesso!');
    }
}
