<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AgentService;  // Certifique-se de que a classe AgentService está sendo usada corretamente
use App\Models\User;  // Certifique-se de que a classe User está configurada corretamente
use App\Models\UserAgent;  // Certifique-se de que a classe UserAgent está configurada corretamente

class AgentController extends Controller
{
    protected $agentService;

    // Injeta o AgentService no Controller
    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    // Função para pausar/despausar o agente
    public function pauseAgent(Request $request)
    {
        // Valida os parâmetros recebidos
        $request->validate([
            'agent' => 'required|string|max:255',  // O identificador do agente (ex: 'Agent/1001')
            'paused' => 'required|boolean',       // Se o agente deve ser pausado ou despausado (1 ou 0)
            'reason' => 'nullable|string|max:255', // Motivo opcional
        ]);

        // Chama o serviço para pausar/despausar o agente
        $response = $this->agentService->pauseAgent(
            $request->input('agent'), 
            $request->input('paused'),
            $request->input('reason')
        );

        // Retorna a resposta do serviço
        return response()->json($response);
    }

    // Função para exibir todos os agentes e usuários
    public function index()
    {
        $users = User::all();  // Obtém todos os usuários
        $userAgents = UserAgent::all(); // Obtém todas as associações entre usuários e agentes
        
        return view('agents.index', compact('users', 'userAgents'));
    }

    // Função para associar um usuário ao agente
    public function store(Request $request)
    {
        // Validação dos dados recebidos no formulário
        $request->validate([
            'user_id' => 'required|exists:users,id',  // Certifique-se de que o usuário existe
            'agent' => 'required|string|max:255',    // Validação para o campo de agente
        ]);

        // Criação de uma nova associação (UserAgent)
        UserAgent::create([
            'user_id' => $request->user_id,  // ID do usuário selecionado
            'agent' => $request->agent,      // Agente fornecido
        ]);

        // Redireciona com uma mensagem de sucesso
        return redirect()->route('agents.index')->with('success', 'Usuário vinculado ao agente com sucesso!');
    }

    // Função para excluir a associação de um agente
    public function destroy($id)
    {
        // Encontre o UserAgent a ser excluído
        $userAgent = UserAgent::findOrFail($id);
        
        // Exclua o registro de associação
        $userAgent->delete();
        
        // Redirecione com uma mensagem de sucesso
        return redirect()->route('agents.index')->with('success', 'Associação removida com sucesso!');
    }
}
