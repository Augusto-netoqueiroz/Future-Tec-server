<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $ramais = DB::table('sippeers')
            ->where('modo', 'ramal')
            ->leftJoin('users', 'sippeers.user_id', '=', 'users.id')
            ->select('sippeers.id', 'sippeers.name as ramal', 'sippeers.ipaddr', 'sippeers.context', 'users.name as atendente')
            ->get();

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

    DB::table('sippeers')->where('id', $id)->update([
        'name' => $request->name,
        'secret' => $request->secret,
        'context' => $request->context,
    ]);

    return redirect()->route('ramais.index')->with('success', 'Ramal atualizado com sucesso!');
}

    public function store(Request $request)
    {
        $request->validate([
            'ramal' => 'required|string|max:255|unique:sippeers,name',
            'senha' => 'required|string|max:255',
            'context' => 'required|string|max:255',
        ]);

        DB::table('sippeers')->insert([
            'name' => $request->ramal,
            'secret' => $request->senha,
            'host' => 'dynamic',
            'context' => $request->context,
            'ipaddr' => '',
            'modo' => 'ramal', 
        ]);

        return redirect()->route('ramais.index')->with('success', 'Ramal criado com sucesso!');
    }

    public function destroy($id)
    {
        DB::table('sippeers')->where('id', $id)->delete();

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
        $troncos = DB::table('sippeers')
            ->where('type', 'friend') // Filtra apenas troncos
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
            'host' => 'required|string|max:255',
        ]);

        DB::table('sippeers')->insert([
            'name' => $request->nome,
            'secret' => $request->senha,
            'host' => $request->host,
            'context' => $request->context,
            'type' => 'friend',
            'qualify' => 'yes',
            'ipaddr' => '',
            'modo' => 'tronco', 
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
    
        DB::table('sippeers')->where('id', $id)->update([
            'name' => $request->nome,
            'secret' => $request->senha,
            'host' => $request->host,
            'context' => $request->context,
        ]);
    
        return redirect()->route('troncos.index')->with('success', 'Tronco atualizado com sucesso!');
    }

    public function destroytronco($id)
    {
        DB::table('sippeers')->where('id', $id)->delete();

        return redirect()->route('troncos.index')->with('success', 'Tronco excluído com sucesso!');
    }


}
