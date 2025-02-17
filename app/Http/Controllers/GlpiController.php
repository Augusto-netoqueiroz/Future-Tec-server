<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GlpiService;
use Illuminate\Support\Facades\Log;

class GlpiController extends Controller {
    protected $glpiService;




    public function index(Request $request)
    {
        // Obtendo filtros da requisição
        $userId = $request->input('user_id');
        $entityId = $request->input('entity_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        try {
            $users = $this->glpiService->getUsers();
            $entities = $this->glpiService->getEntities();
    
            // Inicializa os tickets como uma coleção vazia
            $tickets = collect();
    
            // Só chama a API se algum filtro for preenchido
            if ($userId || $entityId || $startDate || $endDate) {
                $tickets = $this->glpiService->getTickets($userId, $entityId, $startDate, $endDate);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar dados: ' . $e->getMessage());
        }
    
        return view('glpi.tickets', compact('tickets', 'users', 'entities'));
    }
    
    
    

    public function filterTickets(Request $request)
    {
        try {
            // Busca os tickets filtrados no serviço GLPI
            $response = $this->glpiService->filterTickets(
                $request->input('user_id'),
                $request->input('entity_id'),
                $request->input('start_date'),
                $request->input('end_date')
            );
    
            // Verifica se há dados válidos
            if (empty($response['data']) || !is_array($response['data'])) {
                return response()->json(['data' => []]);
            }
    
            // Acessa os tickets dentro de "data"
            $tickets = $response['data'];
    
            // Formata os tickets corretamente
            $formattedTickets = [];
    
            foreach ($tickets as $ticket) {
                $formattedTickets[] = [
                    'id' => $ticket["2"] ?? 'N/A',
                    'titulo' => $ticket["1"] ?? 'Sem título',
                    'requerente' => $ticket["4"] ?? 'N/A',
                    'categoria' => $ticket["7"] ?? 'N/A',
                    'origem' => $ticket["9"] ?? 'N/A',
                    'descricao' => $ticket["21"] ?? 'Sem descrição',
                    'entidade' => $ticket["80"] ?? 'N/A',
                    'status' => $ticket["12"] ?? 'N/A', // Adicionado o status
                    'data_abertura' => isset($ticket["15"]) && is_string($ticket["15"]) 
                        ? date('d/m/Y H:i', strtotime($ticket["15"])) 
                        : 'N/A'
                ];
            }
    
            return response()->json(['data' => $formattedTickets]);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao carregar tickets: ' . $e->getMessage()], 500);
        }
    }
    
    

    



    public function showCreateTicketForm() {
        try {
            // Buscar entidades e usuários da API
            $entities = $this->glpiService->getEntities();
            $users = $this->glpiService->getUsers();
            $categories = $this->glpiService->getCategories();
    
            return view('glpi.index', compact('entities', 'users', 'categories'));
    
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar entidades e usuários: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar entidades e usuários.');
        }
    }
    


    public function __construct(GlpiService $glpiService) {
        $this->glpiService = $glpiService;
    }

   

    public function createTicket(Request $request) {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'user_id'     => 'required|integer',
            'entities_id' => 'required|integer',
            'category_id' => 'required|integer',
            'status'      => 'required|in:pendente,solucionado'
        ]);
    
        try {
            // Mapeia a escolha do usuário para o status do GLPI
            $statusMap = [
                'pendente'   => 4, // Aguardando
                'solucionado' => 5 // Resolvido
            ];
            $status = $statusMap[$request->input('status')];
    
            // Criar o chamado no GLPI
            $ticket = $this->glpiService->createTicket(
                $request->input('title'),
                $request->input('description'),
                $request->input('user_id'),
                $request->input('entities_id'),
                $request->input('category_id'),
                $status
            );
    
            if (!$ticket || !isset($ticket['id'])) {
                return redirect()->back()->with('error', 'Falha ao criar o ticket.');
            }
    
            // Armazena o ID do chamado
            $ticketId = $ticket['id'];
    
            // Associa o chamado ao usuário
            $association = $this->glpiService->associateUserToTicket($ticketId, $request->input('user_id'));
    
            if (!$association) {
                return redirect()->back()->with([
                    'error'  => 'Chamado criado, mas falha ao associar usuário.',
                    'ticket' => $ticket
                ]);
            }
    
            return redirect()->back()->with([
                'success' => 'Ticket criado e associado com sucesso!',
                'ticket'  => $ticket
            ]);
    
        } catch (\Exception $e) {
            Log::error('Erro ao criar ticket: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro interno ao criar o ticket.');
        }
    }
    
    

    // Buscar um chamado por ID
    public function getTicket($id) {
        try {
            $ticket = $this->glpiService->getTicket($id);

            if (!$ticket) {
                return response()->json(['error' => 'Ticket não encontrado'], 404);
            }

            return response()->json($ticket);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar ticket: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno ao buscar o ticket'], 500);
        }
    }

    // Listar todos os chamados
    public function listTickets() {
        try {
            $tickets = $this->glpiService->listTickets();
            return response()->json($tickets);
        } catch (\Exception $e) {
            Log::error('Erro ao listar tickets: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno ao listar tickets'], 500);
        }
    }


    
}
