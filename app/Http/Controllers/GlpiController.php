<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GlpiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

use App\Models\DiscordMessage;
 



use Illuminate\Support\Facades\Http; // Importação correta
use Carbon\Carbon;

class GlpiController extends Controller {
    protected $glpiService;



 
    
    public function index(Request $request)
    {

        if (!Auth::check()) {
            return redirect()->route('login'); // Redireciona para a tela de login se não estiver autenticado
        }
        
        $userId = $request->input('user_id');
        $entityId = $request->input('entity_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        try {
            $users = $this->glpiService->getUsers();
            $entities = $this->glpiService->getEntities();
    
            // Obtendo os tickets e garantindo que seja uma coleção
            $tickets = [];
    
            if ($userId || $entityId || $startDate || $endDate) {
                $tickets = $this->glpiService->getTickets($userId, $entityId, $startDate, $endDate);
            }
    
            // Convertendo para Collection para usar métodos do Laravel
            $tickets = collect($tickets); 
    
            // Implementação da paginação manual
            $perPage = 10; // Define o número de registros por página
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $tickets->slice(($currentPage - 1) * $perPage, $perPage)->values();
    
            $tickets = new LengthAwarePaginator(
                $currentItems,
                $tickets->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
    
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar dados: ' . $e->getMessage());
        }
    
        return view('glpi.tickets', compact('tickets', 'users', 'entities'));
    }
    
    
    public function filterTickets(Request $request)
{
    try {
        $page = $request->input('page', 1); // Página atual
        $perPage = 10; // Defina quantos tickets por página

        // Buscar os tickets filtrados no serviço GLPI
        $response = $this->glpiService->filterTickets(
            $request->input('user_id'),
            $request->input('entity_id'),
            $request->input('start_date'),
            $request->input('end_date')
        );

        // Verifica se há dados válidos
        if (empty($response['data']) || !is_array($response['data'])) {
            return response()->json([
                'data' => [],
                'current_page' => $page,
                'last_page' => 1,
                'total' => 0
            ]);
        }

        // Acessa os tickets dentro de "data"
        $tickets = $response['data'];

        // Formata os tickets corretamente
        $formattedTickets = array_map(function ($ticket) {
            return [
                'id' => $ticket["2"] ?? 'N/A',
                'titulo' => $ticket["1"] ?? 'Sem título',
                'requerente' => $ticket["4"] ?? 'N/A',
                'categoria' => $ticket["7"] ?? 'N/A',
                'origem' => $ticket["9"] ?? 'N/A',
                'descricao' => $ticket["21"] ?? 'Sem descrição',
                'entidade' => $ticket["80"] ?? 'N/A',
                'status' => $ticket["12"] ?? 'N/A',
                'data_abertura' => isset($ticket["15"]) && is_string($ticket["15"]) 
                    ? date('d/m/Y H:i', strtotime($ticket["15"])) 
                    : 'N/A'
            ];
        }, $tickets);

        // Criar instância do LengthAwarePaginator
        $total = count($formattedTickets);
        $paginatedData = new LengthAwarePaginator(
            array_slice($formattedTickets, ($page - 1) * $perPage, $perPage), 
            $total, 
            $perPage, 
            $page, 
            ['path' => url()->current()]
        );

        return response()->json($paginatedData);

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
    
            // **Registrar a atividade no banco**
            DB::table('atividade')->insert([
                'user_id'   => Auth::id(),
                'acao'      => 'Criação de Ticket', // Agora estamos preenchendo o campo obrigatório
                'descricao' => "Usuário " . Auth::user()->name . " criou o ticket #$ticketId - " . $request->input('title'),
                'ip'        => $request->ip(), // CORRIGIDO: Agora pega o IP corretamente
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
    
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


    public function updateTicket(Request $request, $id)
{
    $request->validate([
        'title'       => 'required|string|max:255',
        'description' => 'required|string',
        'status'      => 'required|in:1,2,3,4,5,6',
        'category'    => 'nullable|integer',
        'user'        => 'nullable|integer',
        'entity'      => 'nullable|integer'
    ]);

    try {
        $ticketData = [
            'name'              => $request->input('title'),
            'content'           => $request->input('description'),
            'status'            => (int) $request->input('status'),
            'itilcategories_id' => (int) $request->input('category'),
            'users_id_recipient'=> (int) $request->input('user'),
            'entities_id'       => (int) $request->input('entity')
        ];

        $updatedTicket = $this->glpiService->updateTicket($id, $ticketData); 

        // **Registrar a atividade no banco**
        DB::table('atividade')->insert([
            'user_id'   => Auth::id(),
            'acao'      => 'Atualização de Ticket',
            'descricao' => "Usuário " . Auth::user()->name . " atualizou o ticket #$id - " . $request->input('title'),
            'ip'        => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('glpi.tickets', $id)->with('success', 'Ticket atualizado com sucesso!');

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Erro ao atualizar ticket: ' . $e->getMessage());
    }
}
    
    


public function deleteTicket($id) {
    try {
        $this->glpiService->deleteTicket($id);

        // **Registrar a atividade no banco**
        DB::table('atividade')->insert([
            'user_id'   => Auth::id(),
            'acao'      => 'Exclusão de Ticket',
            'descricao' => "Usuário " . Auth::user()->name . " deletou o ticket #$id",
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('glpi.tickets')->with('success', 'Ticket deletado com sucesso!');

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Erro ao deletar ticket: ' . $e->getMessage());
    }
}

    
    public function edit($id)
{
    try {
        $ticket = $this->glpiService->getSingleTicket($id);
        $entities = $this->glpiService->getEntities();
        $users = $this->glpiService->getUsers();
        $categories = $this->glpiService->getCategories();

        if (!$ticket) {
            return redirect()->back()->with('error', 'Ticket não encontrado.');
        }

        // 🔹 Mapeamento dos status
        $statusMap = [
            1 => 'Novo',
            2 => 'Em andamento (atribuído)',
            3 => 'Em andamento (planejado)',
            4 => 'Pendente',
            5 => 'Solucionado',
            6 => 'Fechado'
        ];
        $ticket['status_name'] = $statusMap[$ticket['status']] ?? 'Desconhecido';

        // 🔹 Encontrar o nome da entidade
        $entity = collect($entities)->firstWhere('id', $ticket['entities_id']);
        $ticket['entity_name'] = $entity['name'] ?? 'Desconhecida';

        // 🔹 Encontrar o nome do usuário responsável
        $user = collect($users)->firstWhere('id', $ticket['users_id_recipient']);
        $ticket['user_name'] = $user['name'] ?? 'Não atribuído';

        // 🔹 Encontrar o nome da categoria
        $category = collect($categories)->firstWhere('id', $ticket['itilcategories_id']);
        $ticket['category_name'] = $category['name'] ?? 'Sem categoria';

        return view('glpi.edit', compact('ticket', 'categories', 'entities', 'users'));

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Erro ao carregar ticket: ' . $e->getMessage());
    }
}

    


  

public function sendDailyTicketSummary()
{
    try {
        // Define o intervalo de datas para o dia anterior com hora específica
        $startDate = Carbon::yesterday()->startOfDay()->addSecond()->toDateTimeString();  // 00:00:01
        $endDate = Carbon::yesterday()->endOfDay()->subSecond()->toDateTimeString();      // 23:59:59

        // Buscar os tickets do dia anterior
        $response = $this->glpiService->filterTickets(null, null, $startDate, $endDate);

        if (empty($response['data']) || !is_array($response['data'])) {
            return response()->json(['message' => 'Nenhum ticket encontrado para o dia anterior.']);
        }

        $tickets = $response['data'];

        // Inicializa os contadores
        $entityCounts = [];
        $statusCounts = [];
        $originCounts = [];
        $userCounts = [];

        // Buscar todos os usuários e extrair apenas id e name
        $users = $this->glpiService->getUsers();
        $userNames = [];
        
        foreach ($users as $user) {
            // Extrair apenas id e name dos usuários
            $userNames[$user['id']] = $user['name'];
        }

        // Contagem e categorização dos tickets
        foreach ($tickets as $ticket) {
            // Extrair o título do ticket
            $title = $ticket["1"] ?? 'N/A'; // Título do ticket

            // Extrair origem e status do título com uma abordagem mais flexível
            preg_match('/Origem:\s*([^\s]+)[^\n]*Status:\s*([^\s]+)/', $title, $matches);

            $origin = isset($matches[1]) ? trim($matches[1]) : 'N/A'; // Origem
            $status = isset($matches[2]) ? trim($matches[2]) : 'N/A'; // Status

            // Definir emojis para origem e status
            if ($origin === 'TELEFONE') {
                $origin = "📞 TELEFONE";
            } elseif ($origin === 'CHAT') {
                $origin = "💻 CHAT";
            }

            if ($status === 'SOLUCIONADO') {
                $status = "✅ SOLUCIONADO";
            } elseif ($status === 'PENDENTE') {
                $status = "❌ PENDENTE";
            }

            // Entidade
            $entity = $ticket["80"] ?? 'N/A';
            $entityCounts[$entity] = ($entityCounts[$entity] ?? 0) + 1;

            // Status
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

            // Origem
            $originCounts[$origin] = ($originCounts[$origin] ?? 0) + 1;

            // Usuário que registrou o ticket
            $userId = $ticket["4"] ?? 'N/A';

            // Procurar nome do usuário pelo ID
            $userName = $userNames[$userId] ?? 'Desconhecido';

            $userCounts[$userName] = ($userCounts[$userName] ?? 0) + 1;
        }

        // Organizando os 5 usuários que mais registraram tickets
        arsort($userCounts);
        $topUsers = array_slice($userCounts, 0, 5, true);

        // Gerar o resumo
        $summary = "📋 *Resumo de Tickets - " . Carbon::yesterday()->format('d/m/Y') . "*\n\n";
        $summary .= "🔹 *Total de Tickets:* " . count($tickets) . "\n\n";

        // Contagem por Entidade
        foreach ($entityCounts as $entity => $count) {
            $summary .= "🔸 *$entity:* $count\n";
        }

        $summary .= "\n";

        // Contagem por Status
        foreach ($statusCounts as $status => $count) {
            $summary .= "*$status:* $count\n";
        }

        $summary .= "\n";

        // Contagem por Origem
        foreach ($originCounts as $origin => $count) {
            $summary .= "*$origin:* $count\n";
        }

        $summary .= "\n";

        // Top 5 Usuários
        $summary .= "🔝 *Top 5 Usuários que mais registraram Tickets:*\n";
        foreach ($topUsers as $user => $count) {
            $summary .= "👤 $user - $count tickets\n";
        }

        // Dados para a API do WhatsApp
        $whatsappData = [
            'number' => '120363411854834117@g.us', // Substituir pelo número desejado
            'body' => $summary,
            'saveOnTicket' => true,
            'linkPreview' => true
        ];

        // Enviar o resumo via API do WhatsApp
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('WHATSAPP_API_TOKEN'),
            'Content-Type' => 'application/json'
        ])->post('https://chat.core.bsb.br:443/backend/api/messages/send', $whatsappData);

        if ($response->successful()) {
            return response()->json(['message' => 'Resumo enviado com sucesso!']);
        } else {
            return response()->json(['error' => 'Falha ao enviar resumo.', 'details' => $response->body()], 500);
        }

    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao processar tickets: ' . $e->getMessage()], 500);
    }
}

 
public function getDiscordMessages()
{
    $url = env('DISCORD_URL');
    $token = env('DISCORD_TOKEN');

    if (!$url || !$token) {
        Log::error('Configuração da API do Discord ausente');
        return response()->json(['error' => 'Configuração da API do Discord ausente'], 500);
    }

    Log::info('Iniciando busca de mensagens no Discord');

    $response = Http::withHeaders([
        'Authorization' => "Bot $token",
    ])->get($url);

    if ($response->failed()) {
        Log::error('Falha ao buscar os dados do Discord', ['status' => $response->status()]);
        return response()->json(['error' => 'Falha ao buscar os dados do Discord'], $response->status());
    }

    $messages = $response->json();
    Log::info('Mensagens recebidas', ['count' => count($messages)]);

    foreach ($messages as $message) {
        if (!isset($message['content'])) {
            continue;
        }

        $content = $message['content'];

        // Mapeamento dos campos que queremos extrair
        $fields = [
            'EMPRESA' => 'empresa',
            'PROTOCOLO' => 'protocolo',
            'CLIENTE' => 'cliente',
            'CPF' => 'cpf',
            'QUEM LIGOU' => 'quem_ligou',
            'DESCRIÇÃO' => 'descricao',
            'CATEGORIA' => 'categoria',
            'STATUS' => 'status',
            'ATT' => 'att',
            'TELEFONE' => 'telefone',
            'ENDEREÇO' => 'endereco'
        ];

        $data = [];
        
        // Percorre linha por linha e extrai o conteúdo após o ":"
        foreach (explode("\n", $content) as $line) {
            foreach ($fields as $label => $key) {
                if (str_starts_with($line, "$label:")) {
                    $value = trim(str_replace("$label:", '', $line)); // Remove a chave e espaços extras
                    $data[$key] = $value ?: null; // Se o valor for vazio, armazena como null
                }
            }
        }

        // Se o campo EMPRESA estiver vazio, ignorar a mensagem
        if (empty($data['empresa'])) {
            Log::warning('Mensagem ignorada - EMPRESA vazia', ['content' => $content]);
            continue;
        }

        // Verifica duplicação antes de salvar, considerando a ausência do campo 'descricao'
        $existingMessage = DiscordMessage::where('protocolo', $data['protocolo']);

        if (!empty($data['descricao'])) {
            $existingMessage = $existingMessage->where('descricao', $data['descricao']);
        }

        $existingMessage = $existingMessage->first();

        if ($existingMessage) {
            Log::warning('Mensagem duplicada ignorada', [
                'protocolo' => $data['protocolo'],
                'descricao' => $data['descricao'] ?? 'N/A',
            ]);
            continue;
        }

        // Criando novo registro
        $msg = DiscordMessage::create($data);

        Log::info('Mensagem salva no banco', ['id' => $msg->id]);
    }

    return response()->json(['message' => 'Mensagens processadas e armazenadas']);
}


public function listMessages()
{
    $messages = DiscordMessage::latest()->get();
    return view('discord', compact('messages'));
}


/**
 * Retorna apenas as mensagens pendentes (ticket=null) ou falhas (ticket='falha'),
 * já convertidas em array pronto para iterar.
 */
public function getDiscordbanco()
{
    // Traz somente os registros onde ticket está nulo ou 'falha'
    $messagesDB = DiscordMessage::whereNull('ticket')
        ->orWhere('ticket', 'falha')
        ->get();

    // Mapeamos para o formato que o processAndCreateTickets espera
    $messages = $messagesDB->map(function ($message) {
        return [
            'id'          => $message->id,
            // O título agora será sempre sobrescrito no processAndCreateTickets,
            // então aqui não importa muito, mas podemos continuar retornando algo
            'title'       => $message->protocolo ?? 'Sem título',
            'description' => [
                'descricao'   => $message->descricao,
                'quem_ligou'  => $message->quem_ligou,
                'cpf'         => $message->cpf,
                'cliente'     => $message->cliente,
                'telefone'    => $message->telefone,
                'endereco'    => $message->endereco,
            ],
            // Se 'att' for o nome do usuário, buscaremos por 'name' no getAllData
            'user_id'     => $message->att,
            'entities_id' => $message->empresa,
            'category_id' => $message->categoria,
            // Pode vir "SOLUCIONADO", "(SOLUCIONADO)", "PENDENTE", "(PENDENTE)"
            'status'      => $message->status
        ];
    })->all(); // array puro

    return $messages;
}

/**
 * Cria tickets no GLPI usando os dados do Discord,
 * e atualiza o campo 'ticket' na tabela para 'criado' ou 'falha'.
 */
public function processAndCreateTickets()
{
    Log::info('Iniciando processo completo de criação de tickets');

    // $messages agora é um array
    $messages = $this->getDiscordbanco();
    $data = $this->getAllData(); // ['entities' => [...], 'users' => [...], 'categories' => [...]]

    if (
        !isset($data['entities'], $data['users'], $data['categories']) ||
        !is_array($data['entities']) || !is_array($data['users']) || !is_array($data['categories'])
    ) {
        // Log local
        $logMessage = 'Estrutura de dados inválida';
        Log::error($logMessage, ['data' => $data]);

        // Salva na tabela ticket_logs
        DB::table('ticket_logs')->insert([
            'message_id'  => null,
            'log_message' => $logMessage,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['error' => $logMessage], 500);
    }

    $logs = [];

    foreach ($messages as $message) {
        $messageId = $message['id']; // ID do registro no banco discord_messages

        $user     = collect($data['users'])->firstWhere('name', $message['user_id']);
        $entity   = collect($data['entities'])->firstWhere('name', $message['entities_id']);
        $category = collect($data['categories'])->firstWhere('name', $message['category_id']);

        // Verifica se algum deles está faltando
        if (!$user || !$entity || !$category) {
            // Monta lista do que está faltando
            $faltando = [];
            if (!$user) {
                $faltando[] = 'Usuário';
            }
            if (!$entity) {
                $faltando[] = 'Entidade';
            }
            if (!$category) {
                $faltando[] = 'Categoria';
            }

            $faltandoStr = implode(', ', $faltando);
            $logMessage = "Dados incompletos para criação do ticket (ID=$messageId). Faltando: $faltandoStr";
            $logs[] = $logMessage;

            // Salva log na tabela
            DB::table('ticket_logs')->insert([
                'message_id'  => $messageId,
                'log_message' => $logMessage,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            Log::warning($logMessage, ['message' => $message]);

            // Marca como 'falha'
            DiscordMessage::where('id', $messageId)->update(['ticket' => 'falha']);
            continue;
        }

        // Título fixo
        $title       = "CRIADO AUTOMATICAMENTE - FT SERVER";
        // Descrição em JSON
        $description = json_encode($message['description'] ?? 'Sem descrição');

        // IDs internos do GLPI
        $userId     = $user['id'];
        $entityId   = $entity['id'];
        $categoryId = $category['id'];

        // Mapeamento de status
        $rawStatus = strtoupper(trim($message['status'] ?? '', " ()"));
        if ($rawStatus === 'SOLUCIONADO') {
            $statusGlpi = 5; // Solucionado
        } elseif ($rawStatus === 'PENDENTE') {
            $statusGlpi = 4; // Pendente
        } else {
            $statusGlpi = 1; // Novo (default)
        }

        try {
            // Cria o ticket no GLPI
            $response = $this->glpiService->createTicket(
                $title,
                $description,
                $userId,
                $entityId,
                $categoryId,
                $statusGlpi
            );

            if (!empty($response['id'])) {
                // Associação de requerente
                $this->glpiService->associateUserToTicket($response['id'], $userId);

                $logMessage = "Ticket GLPI #{$response['id']} criado (ID=$messageId) e requerente associado.";
                $logs[] = $logMessage;

                // Salva na tabela ticket_logs
                DB::table('ticket_logs')->insert([
                    'message_id'  => $messageId,
                    'log_message' => $logMessage,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                // Marca como 'criado'
                DiscordMessage::where('id', $messageId)->update(['ticket' => 'criado']);

            } else {
                $logMessage = "Falha ao criar ticket GLPI (sem ID retornado) (ID=$messageId)";
                $logs[] = $logMessage;

                DB::table('ticket_logs')->insert([
                    'message_id'  => $messageId,
                    'log_message' => $logMessage,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                DiscordMessage::where('id', $messageId)->update(['ticket' => 'falha']);
            }

        } catch (\Exception $e) {
            $logMessage = "Erro ao criar ticket: " . $e->getMessage() . " (ID=$messageId)";
            $logs[] = $logMessage;

            DB::table('ticket_logs')->insert([
                'message_id'  => $messageId,
                'log_message' => $logMessage,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            Log::error($logMessage, [
                'error' => $e->getMessage(),
                'messageId' => $messageId,
                'payload' => compact('title','description','userId','entityId','categoryId','statusGlpi')
            ]);

            // Marca como 'falha'
            DiscordMessage::where('id', $messageId)->update(['ticket' => 'falha']);
        }
    }

    Log::info('Processo completo de criação de tickets finalizado');
    return response()->json(['logs' => $logs]);
}





    public function getAllData()
    {
        return [
            'entities'  => $this->glpiService->getEntitiesName(),
            'users'     => $this->glpiService->getUsersName(),
            'categories' => $this->glpiService->getCategoriesName(),
        ];
    }

    
    
}
