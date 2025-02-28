<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Call;



class PainelAtendimentoController extends Controller
{


    
    // Exibe a página do painel de atendimento com os ramais disponíveis
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login'); // Redireciona para a tela de login se não estiver autenticado
        }
        
        $userId = Auth::id();
        $empresaId = Auth::user()->empresa_id;
    
        $ramalAssociado = DB::table('sippeers')
            ->where('user_id', $userId)
            ->where('empresa_id', $empresaId) // Filtra pelos ramais da mesma empresa
            ->first();
    
        if ($ramalAssociado) {
            $ramaisOnline = collect([$ramalAssociado]);
        } else {
            $ramaisOnline = DB::table('sippeers')
                ->where('modo', 'ramal')
                ->whereNotNull('ipaddr')
                ->whereRaw('LENGTH(ipaddr) > 6')
                ->whereNull('user_name')
                ->where('empresa_id', $empresaId) // Filtra pelos ramais da mesma empresa
                ->get();
        }
    
        return view('painel-atendimento', compact('ramaisOnline'));
    }
    

    // Associa o usuário autenticado a um ramal
    public function associar(Request $request)
{
    $userId = Auth::id();
    $userName = Auth::user()->name;

    $request->validate([
        'ramal_id' => 'required|exists:sippeers,id',
    ]);

    $ramal = DB::table('sippeers')->where('id', $request->ramal_id)->first();

    if ($ramal->user_id !== null || $ramal->user_name !== null) {
        return redirect()->route('painel-atendimento')->with('error', 'Este ramal já está associado a outro usuário!');
    }

    DB::table('sippeers')
        ->where('id', $request->ramal_id)
        ->update([
            'user_id' => $userId,
            'user_name' => $userName,
        ]);

    // Insere o registro na tabela agente_ramal_vinculo
    DB::table('agente_ramal_vinculo')->insert([
        'agente_id' => $userId,
        'ramal_id' => $request->ramal_id,
        'inicio_vinculo' => now(),
    ]);

    // Atualiza ou insere na tabela queue_members
    $queueMember = DB::table('queue_members')->where('member_name', $userName)->first();

    if ($queueMember) {
        DB::table('queue_members')
            ->where('member_name', $userName)
            ->update([
                'interface' => 'SIP/' . $ramal->name,
            ]);
    } else {
        DB::table('queue_members')->insert([
            'queue_name' => $ramal->queue_name ?? 'default_fila',
            'interface' => 'SIP/' . $ramal->name,
            'member_name' => $userName,
        ]);
    }

    return redirect()->route('painel-atendimento')->with('success', 'Ramal associado com sucesso!');
}


    // Desassocia o usuário autenticado do ramal (ao fazer logout)
    public function desassociar(Request $request)
{
    $userId = Auth::id();
    $userName = Auth::user()->name;

    $ramal = DB::table('sippeers')
        ->where('user_id', $userId)
        ->where('user_name', $userName)
        ->first();

    if ($ramal) {
        DB::table('sippeers')
            ->where('id', $ramal->id)
            ->update([
                'user_id' => null,
                'user_name' => null,
            ]);

        DB::table('queue_members')
            ->where('member_name', $userName)
            ->update([
                'interface' => null,
                'state_interface' => null,
            ]);

        // Atualiza o término do vínculo na tabela agente_ramal_vinculo
        DB::table('agente_ramal_vinculo')
            ->where('agente_id', $userId)
            ->where('ramal_id', $ramal->id)
            ->whereNull('fim_vinculo')
            ->update([
                'fim_vinculo' => now(),
            ]);

        return redirect()->route('painel-atendimento')->with('success', 'Ramal desassociado com sucesso!');
    } else {
        return redirect()->route('painel-atendimento')->with('error', 'Nenhum ramal encontrado para desassociar.');
    }
}



public function storeCall(Request $request)
    {
        
        $call = Call::create([
            'user_name' => $request->user_name,
            'ramal' => $request->ramal,
            'calling_to' => $request->calling_to,
            'queue_name' => $request->queue_name ?? 'Sem fila',
            'call_duration' => $request->call_duration ?? '00:00',
            'channel' => $request->channel,
        ]);

        return response()->json(['message' => 'Ligação salva com sucesso!', 'call' => $call]);
    }

    public function getUserCalls()
    {
        $userName = auth()->user()->name;
        $calls = Call::where('user_name', $userName)->orderByDesc('created_at')->get();
        
        return response()->json($calls);
    }


}
