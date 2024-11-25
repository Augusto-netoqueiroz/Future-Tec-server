<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PainelAtendimentoController extends Controller
{
    // Exibe a página do painel de atendimento com os ramais disponíveis
    public function index()
{
    // Recupera o ID do usuário autenticado
    $userId = Auth::id();

    // Verifica se o usuário já está associado a um ramal
    $ramalAssociado = DB::table('sippeers')
        ->where('user_id', $userId)
        ->first();

    // Caso o usuário já tenha um ramal associado, exibe somente ele
    if ($ramalAssociado) {
        $ramaisOnline = collect([$ramalAssociado]);
    } else {
        // Caso contrário, exibe somente os ramais disponíveis
        $ramaisOnline = DB::table('sippeers')
            ->whereNotNull('ipaddr') // Ramal precisa ter um IP (indica que está online)
            ->whereRaw('LENGTH(ipaddr) > 6') // Verifica se o IP tem mais de 6 caracteres (online)
            ->whereNull('user_name') // Ramal não deve estar associado a nenhum usuário
            ->get();
    }

    return view('painel-atendimento', compact('ramaisOnline'));
}

    // Associa o usuário autenticado a um ramal
    public function associar(Request $request)
    {
        // Verifica se o usuário autenticado está tentando associar o ramal
        $userId = Auth::id(); // Recupera o ID do usuário autenticado
        $userName = Auth::user()->name; // Recupera o nome do usuário autenticado
        
        // Validação
        $request->validate([
            'ramal_id' => 'required|exists:sippeers,id', // Verifica se o ramal existe
        ]);

        // Verifica se o ramal não está associado a outro usuário
        $ramal = DB::table('sippeers')->where('id', $request->ramal_id)->first();

        if ($ramal->user_id !== null || $ramal->user_name !== null) {
            return redirect()->route('painel-atendimento')->with('error', 'Este ramal já está associado a outro usuário!');
        }

        // Associa o user_id e user_name ao ramal
        DB::table('sippeers')
            ->where('id', $request->ramal_id)
            ->update([
                'user_id' => $userId,
                'user_name' => $userName,
            ]);

        return redirect()->route('painel-atendimento')->with('success', 'Ramal associado com sucesso!');
    }

  // Desassocia o usuário autenticado do ramal (ao fazer logout)
public function desassociar(Request $request)
{
    // Recupera o ID e nome do usuário autenticado
    $userId = Auth::id();
    $userName = Auth::user()->name;
    
    // Verifica se o ramal realmente está associado ao usuário
    $ramal = DB::table('sippeers')
        ->where('user_id', $userId)
        ->where('user_name', $userName)
        ->first();

    if ($ramal) {
        // Se encontrado, desassocia o user_id e user_name
        DB::table('sippeers')
            ->where('id', $ramal->id)
            ->update([
                'user_id' => null,
                'user_name' => null,
            ]);

        return redirect()->route('painel-atendimento')->with('success', 'Ramal desassociado com sucesso!');
    } else {
        // Caso não encontre a associação do usuário
        return redirect()->route('painel-atendimento')->with('error', 'Nenhum ramal encontrado para desassociar.');
    }
}

}
