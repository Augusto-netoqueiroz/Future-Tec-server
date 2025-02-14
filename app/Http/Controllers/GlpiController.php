<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GlpiService;
use Illuminate\Support\Facades\Log;

class GlpiController extends Controller {
    protected $glpiService;


    public function showCreateTicketForm() {
        try {
            // Buscar entidades e usuários da API
            $entities = $this->glpiService->getEntities();
            $users = $this->glpiService->getUsers();
    
            return view('glpi.index', compact('entities', 'users'));
    
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
            'entities_id' => 'required|integer'
        ]);
    
        try {
            // Criar o chamado no GLPI
            $ticket = $this->glpiService->createTicket(
                $request->input('title'),
                $request->input('description'),
                $request->input('user_id'),
                $request->input('entities_id')
            );
    
            // Verifica se a API retornou um ID de chamado válido
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
