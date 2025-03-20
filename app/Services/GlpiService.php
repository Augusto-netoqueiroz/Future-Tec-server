<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Https;

class GlpiService {
    protected $client;
    protected $apiUrl;
    protected $apiToken;
    protected $sessionToken;

    public function __construct() {
        $this->apiUrl = env('GLPI_URL');
        $this->apiToken = env('GLPI_API_TOKEN', 'SEU_TOKEN_AQUI');

        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'Authorization' => "user_token {$this->apiToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        $this->sessionToken = null;
    }

    protected function getSessionToken() {
        if (!$this->sessionToken) {
            $this->sessionToken = $this->initSession();
        }
        return $this->sessionToken;
    }

    public function initSession() {
        if (session('glpi_session_token')) {
            return session('glpi_session_token');
        }

        $response = Http::withHeaders([
            'App-Token'    => env('GLPI_APP_TOKEN'),
            'Authorization' => 'user_token ' . env('GLPI_USER_TOKEN'),
        ])->get(env('GLPI_URL') . 'initSession');

        if ($response->failed()) {
            throw new \Exception('Erro ao iniciar sessão no GLPI: ' . $response->body());
        }

        $data = $response->json();
        session(['glpi_session_token' => $data['session_token']]);

        return $data['session_token'];
    }




 public function createTicket($title, $description, $user_id, $entities_id, $category_id, $status) {
        $response = $this->client->post('Ticket', [
            'headers' => [
                'App-Token'    => env('GLPI_APP_TOKEN'),
                'Session-Token' => $this->getSessionToken(),
                'Content-Type'  => 'application/json'
            ],
            'json' => [
                'input' => [
                    'name'               => $title,
                    'content'            => $description,
                    'users_id_recipient' => $user_id,
                    'entities_id'        => $entities_id,
                    'itilcategories_id'  => $category_id,
                    'status'             => $status
                ]
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    // Os demais métodos seguem a mesma lógica, trocando "$this->sessionToken" por "$this->getSessionToken()".
    // Exemplo simplificado:

    public function getCategories() {
        $response = $this->client->get('ITILCategory', [
            'headers' => [
                'App-Token'    => env('GLPI_APP_TOKEN'),
                'Session-Token' => $this->getSessionToken(),
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    // Aplique a mesma correção em todos os métodos existentes que usam $this->sessionToken.










public function getTickets($userId = null, $entityId = null, $startDate = null, $endDate = null)
{
    $sessionToken = session('glpi_session_token');

    if (!$sessionToken) {
        throw new \Exception('Session Token não encontrado. Inicie a sessão primeiro.');
    }

    // Parâmetros base
    $params = [
        'range' => '0-999', // Limite de resultados
        'sort'  => 'date_mod', // Ordenar por data de modificação
        'order' => 'DESC', // Ordem decrescente
        'forcedisplay' => 'id,name,users_id_recipient,entities_id,status,date_creation'
    ];

    // Adicionando filtros
    $filters = [];

    if (!empty($userId)) {
        $filters[] = "users_id_recipient=$userId";
    }
    if (!empty($entityId)) {
        $filters[] = "entities_id=$entityId";
    }
    if (!empty($startDate)) {
        $filters[] = "date_mod[>]=" . date('Y-m-d', strtotime($startDate));
    }
    if (!empty($endDate)) {
        $filters[] = "date_mod[<]=" . date('Y-m-d', strtotime($endDate));
    }

    if (!empty($filters)) {
        $params['_filters'] = implode('&', $filters);
    }

    // Montando a URL com query string
    $url = env('GLPI_URL') . '/Ticket?' . http_build_query($params);

    // Fazendo a requisição
    $response = Http::withHeaders([
        'App-Token'     => env('GLPI_APP_TOKEN'),
        'Authorization' => 'user_token ' . env('GLPI_USER_TOKEN'),
        'Session-Token' => $sessionToken,
    ])->get($url);

    if ($response->failed()) {
        throw new \Exception('Erro ao buscar tickets: ' . $response->body());
    }

    return $response->json();
}



public function filterTickets($userId = null, $entityId = null, $startDate = null, $endDate = null)
{
    $sessionToken = session('glpi_session_token');

    if (!$sessionToken) {
        throw new \Exception('Session Token não encontrado. Inicie a sessão primeiro.');
    }

    $params = [
        'range' => '0-999', // Limite de resultados
        
    ];


    // Construindo os critérios de filtragem
    $criteria = [];

    if (!empty($userId)) {
        $criteria[] = [
            'field' => 4, // Campo para usuário requerente
            'searchtype' => 'equals',
            'value' => $userId,
        ];
    }

    if (!empty($entityId)) {
        $criteria[] = [
            'field' => 80, // Campo para entidade
            'searchtype' => 'equals',
            'value' => $entityId,
        ];
    }

    if (!empty($startDate) && !empty($endDate)) {
        $criteria[] = [
            'field' => 15, // Campo para data da criação
            'searchtype' => 'morethan',
            'value' => date('Y-m-d', strtotime($startDate)) . " 00:00:00",
        ];
        $criteria[] = [
            'field' => 15,
            'searchtype' => 'lessthan',
            'value' => date('Y-m-d', strtotime($endDate)) . " 23:59:59",
        ];
    }

   


    // Montando a URL correta para a API do GLPI
    $url = env('GLPI_URL') . 'search/Ticket';

    // Fazendo a requisição HTTP usando POST para enviar os filtros no corpo
    $response = Http::withHeaders([
        'App-Token'    => env('GLPI_APP_TOKEN'),
        'Authorization' => 'user_token ' . env('GLPI_USER_TOKEN'),
        'Session-Token' => $sessionToken,
        'Content-Type'  => 'application/json',
    ])->post($url, [
        'criteria' => $criteria,
        'range' => '0-999',
        'order' => 'DESC',
        'sort' => '15',
    ]);

    if ($response->failed()) {
        throw new \Exception('Erro ao buscar tickets filtrados: ' . $response->body());
    }

    return $response->json();
}



public function updateTicket($id, array $data)
{
    try {
        // A API espera os dados dentro de um array "input"
        $payload = [
            'input' => array_merge(['id' => (int) $id], $data)
        ];

        $response = $this->client->put("Ticket/$id", [
            'headers' => [
                'App-Token'    => env('GLPI_APP_TOKEN'),
                'Session-Token' => $this->sessionToken,
                'Content-Type'  => 'application/json'
            ],
            'json' => $payload // ✅ Envia os dados dentro de "input"
        ]);

        return json_decode($response->getBody()->getContents(), true);

    } catch (\Exception $e) {
        Log::error("Erro ao atualizar ticket ID $id: " . $e->getMessage());
        return null;
    }
}






public function deleteTicket($ticketId) {
    $sessionToken = session('glpi_session_token');

    $response = Http::withHeaders([
        'App-Token'    => env('GLPI_APP_TOKEN'),
        'Authorization' => 'user_token ' . env('GLPI_USER_TOKEN'),
        'Session-Token' => $sessionToken,
    ])->delete(env('GLPI_URL') . "Ticket/$ticketId");

    if ($response->failed()) {
        throw new \Exception('Erro ao deletar ticket: ' . $response->body());
    }

    return $response->json();
}



public function getEntities($range = '0-100') {
    $response = $this->client->get('Entity', [
        'headers' => [
            'App-Token'     => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->getSessionToken(), // ✅ CORRETO AQUI
        ],
        'query' => [
            'range' => $range
        ]
    ]);

    return json_decode($response->getBody(), true);
}



public function getUsers()
{
    $url = 'User?range=0-100&criteria%5B0%5D%5Bfield%5D=8&criteria%5B0%5D%5Bsearchtype%5D=equals&criteria%5B0%5D%5Bvalue%5D=1';

    $response = $this->client->get($url, [
        'headers' => [
            'App-Token' => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->getSessionToken(), // ✅ CORRETO AQUI
        ],
    ]);

    return json_decode($response->getBody(), true);
}



public function associateUserToTicket($ticketId, $userId) {
    $data = [
        "input" => [
            [
                "tickets_id" => $ticketId,
                "users_id"   => $userId,
                "type"       => 1  // Tipo 1 = Solicitante
            ]
        ]
    ];

    $response = $this->client->post('Ticket_User', [
        'headers' => [
            'App-Token'    => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->sessionToken,
            'Content-Type'  => 'application/json'
        ],
        'json' => $data
    ]);

    return json_decode($response->getBody(), true);
}



 


public function getSingleTicket($id)
{
    try {
        // Fazendo a requisição GET para buscar o ticket
        $response = $this->client->get("Ticket/$id", [
            'headers' => [
                'App-Token'    => env('GLPI_APP_TOKEN'),
                'Session-Token' => $this->sessionToken,
            ],
        ]);

        // Converte a resposta para um array associativo
        $ticket = json_decode($response->getBody()->getContents(), true);

        if (!$ticket || !isset($ticket['id'])) {
            return null; // Retorna nulo se o ticket não existir
        }

        return $ticket;
    } catch (\Exception $e) {
        Log::error("Erro ao buscar ticket ID $id: " . $e->getMessage());
        return null;
    }
}



public function getEntitiesName($range = '0-100') {
    $response = $this->client->get('Entity', [
        'headers' => [
            'App-Token'     => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->sessionToken,
        ],
        'query' => [
            'range' => $range
        ]
    ]);

    $entities = json_decode($response->getBody(), true);

    // Criar uma lista de entidades com ID e Nome
    $entitiesList = [];
    foreach ($entities as $entity) {
        $entitiesList[] = [
            'id'   => $entity['id'],
            'name' => $entity['name']
        ];
    }

    return $entitiesList;
}

public function getUsersName()
{
    $url = 'User?range=0-100&criteria%5B0%5D%5Bfield%5D=8&criteria%5B0%5D%5Bsearchtype%5D=equals&criteria%5B0%5D%5Bvalue%5D=1';

    $response = $this->client->get($url, [
        'headers' => [
            'App-Token'    => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->sessionToken,
        ],
    ]);

    $users = json_decode($response->getBody(), true);

    // Criar uma lista de usuários com ID e Nome
    $usersList = [];
    foreach ($users as $user) {
        $usersList[] = [
            'id'   => $user['id'],
            'name' => $user['firstname']
        ];
    }

    return $usersList;
}


public function getCategoriesName()
{
    $response = $this->client->get('ITILCategory', [
        'headers' => [
            'App-Token'    => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->sessionToken,
        ],
    ]);

    $categories = json_decode($response->getBody(), true);

    // Criar uma lista de categorias com ID e Nome
    $categoriesList = [];
    foreach ($categories as $category) {
        $categoriesList[] = [
            'id'   => $category['id'],
            'name' => $category['name']
        ];
    }

    return $categoriesList;
}



}
