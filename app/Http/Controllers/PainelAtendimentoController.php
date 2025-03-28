<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Call;
use Illuminate\Support\Facades\Log;



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
    Log::info('📥 Recebendo chamada para salvar:', $request->all());

    // Buscar o protocolo (uniqueid)
    $protocolo = DB::table('cdr')
        ->where('channel', 'like', '%' . $request->channel . '%')
        ->orderByDesc('calldate')
        ->value('uniqueid');

    // Evitar salvar duplicado
    $existe = Call::where('user_name', $request->user_name)
        ->where('ramal', $request->ramal)
        ->where('calling_to', $request->calling_to)
        ->where('channel', $request->channel)
        ->where('protocolo', $protocolo)
        ->whereDate('created_at', now()->toDateString())
        ->exists();

    if ($existe) {
        Log::info('🔁 Chamada já registrada. Ignorando.');
        return response()->json(['message' => 'Chamada já registrada.']);
    }

    $call = Call::create([
        'user_name' => $request->user_name,
        'ramal' => $request->ramal,
        'calling_to' => $request->calling_to,
        'queue_name' => $request->queue_name ?? 'Sem fila',
        'call_duration' => $request->call_duration ?? '00:00',
        'channel' => $request->channel,
        'protocolo' => $protocolo
    ]);

    Log::info('✅ Chamada salva com sucesso.', ['id' => $call->id]);

    return response()->json(['message' => 'Ligação salva com sucesso!', 'call' => $call]);
}


    public function getUserCalls()
    {
        $userName = auth()->user()->name;
    
        $calls = Call::where('user_name', $userName)
            ->orderByDesc('created_at')
            ->get();
    
        // Adiciona o protocolo (uniqueid) manualmente para cada chamada
        $callsComProtocolo = $calls->map(function ($call) {
            $protocolo = DB::table('cdr')
                ->where('channel', 'like', '%' . $call->channel . '%')
                ->orderByDesc('calldate')
                ->value('uniqueid');
    
            $call->protocolo = $protocolo ?? null;
            return $call;
        });
    
        return response()->json($callsComProtocolo);
    }
    



     

   

    public function buscarProtocolo(Request $request)
    {
        $channel = $request->input('channel');
        Log::info("🔍 Buscando protocolo para canal: " . $channel);
    
        if (!$channel) {
            Log::warning("❌ Canal não informado");
            return response()->json(['error' => 'Canal não informado'], 400);
        }
    
        $protocolo = DB::table('cdr')
            ->where('channel', 'like', "%$channel%")
            ->orderByDesc('calldate')
            ->value('uniqueid');
    
        if (!$protocolo) {
            Log::warning("⚠️ Protocolo não encontrado para canal: " . $channel);
            return response()->json(['error' => 'Protocolo não encontrado'], 404);
        }
    
        Log::info("✅ Protocolo encontrado: " . $protocolo);
        return response()->json(['protocolo' => $protocolo]);
    }
    

}
