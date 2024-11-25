<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RamalController extends Controller
{
    public function consultarEstado()
    {
        // Realiza a consulta na tabela sippeers e busca o nome do usuário associado
        $ramais = DB::table('sippeers')
            ->leftJoin('users', 'sippeers.user_id', '=', 'users.id')  // Fazendo o join com a tabela de usuários
            ->select('sippeers.name as ramal', 'sippeers.ipaddr', 'users.name as atendente')  // Selecionando o nome do usuário
            ->get();

        // Transforma os dados para retornar o estado com base na coluna ipaddr
        $resultado = $ramais->map(function ($ramal) {
            return [
                'ramal' => $ramal->ramal, // Coluna name será usada para o ramal
                'estado' => strlen($ramal->ipaddr) > 6 ? 1 : 0, // 1 para Online, 0 para Offline
                'atendente' => $ramal->atendente, // Incluindo o nome do atendente
            ];
        });

        return response()->json($resultado);
    }

    public function index()
    {
        $ramais = DB::table('sippeers')
            ->leftJoin('users', 'sippeers.user_id', '=', 'users.id')
            ->select('sippeers.id', 'sippeers.name as ramal', 'sippeers.ipaddr', 'users.name as atendente')
            ->get();

        // Processa os dados e inclui o estado do ramal
        $ramais = $ramais->map(function ($ramal) {
            $ramal->estado = strlen($ramal->ipaddr) > 5 ? 'Online' : 'Offline'; // Determina Online ou Offline
            return $ramal;
        });

        return view('ramais.index', compact('ramais'));
    }

    public function create()
    {
        $users = DB::table('users')->get(); // Obtém todos os usuários do banco de dados para associar ao ramal
        return view('ramais.create', compact('users')); // Retorna a view de criação com os usuários
    }

    public function store(Request $request)
    {
        // Validação dos dados recebidos
        $request->validate([
            'ramal' => 'required|string|max:255|unique:sippeers,name', // Garante que o campo "name" seja único
            'senha' => 'required|string|max:255', // Valida a senha
            'context' => 'required|string|max:255', // Valida o contexto
        ]);

        // Inserir o ramal e a senha na tabela 'sippeers'
        DB::table('sippeers')->insert([
            'name' => $request->ramal,   // Nome do ramal
            'secret' => $request->senha, // Senha do ramal
            'host' => 'dynamic',         // Sempre definido como 'dynamic'
            'context' => $request->context, // Contexto definido pelo usuário
            'ipaddr' => '',              // Pode deixar vazio inicialmente, pois o ramal pode não estar registrado (offline)
        ]);

        // Redirecionar de volta com uma mensagem de sucesso
        return redirect()->route('ramais.index')->with('success', 'Ramal criado com sucesso!');
    }

    public function destroy($id)
    {
        // Remove o ramal pelo ID
        DB::table('sippeers')->where('id', $id)->delete();

        return redirect()->route('ramais.index')->with('success', 'Ramal excluído com sucesso!');
    }


//usado para a pagina de listar ramais
public function listarRamais()
{
    // Consulta a tabela de ramais
    $ramais = DB::table('sippeers')
        ->leftJoin('users', 'sippeers.user_id', '=', 'users.id')
        ->select('sippeers.id', 'sippeers.name as ramal', 'sippeers.ipaddr', 'users.name as atendente')
        ->get();

    // Ajusta o estado e o IP para a página de ramais
    $ramais = $ramais->map(function ($ramal) {
        // Define o estado com base na coluna ipaddr
        $ramal->estado = strlen($ramal->ipaddr) > 5 ? 'Online' : 'Offline';
        // Se ipaddr estiver vazio ou nulo, exibe 'Sem IP'
        $ramal->ipaddr = $ramal->ipaddr ?: 'Sem IP';
        return $ramal;
    });

    return view('ramais.index', compact('ramais'));
}


}
