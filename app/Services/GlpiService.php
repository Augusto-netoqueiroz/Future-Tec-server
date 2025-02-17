<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;


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

        $this->sessionToken = $this->initSession();
    }

    // Iniciar sessão na API do GLPI
    public function initSession()
{
    $response = Http::withHeaders([
        'App-Token'    => env('GLPI_APP_TOKEN'),
        'Authorization' => 'user_token ' . env('GLPI_USER_TOKEN'),
    ])->get(env('GLPI_URL') . 'initSession');

    if ($response->failed()) {
        throw new \Exception('Erro ao iniciar sessão no GLPI: ' . $response->body());
    }

    $data = $response->json();
    
    // Armazena o session_token na sessão do Laravel
    session(['glpi_session_token' => $data['session_token']]);

    return $data;
}

public function createTicket($title, $description, $user_id, $entities_id, $category_id, $status) {
    $response = $this->client->post('Ticket', [
        'headers' => [
            'App-Token'    => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->sessionToken,
            'Content-Type'  => 'application/json'
        ],
        'json' => [
            'input' => [
                'name'               => $title,
                'content'            => $description,
                'users_id_recipient' => $user_id,
                'entities_id'        => $entities_id,
                'itilcategories_id'  => $category_id,
                'status'             => $status // Enviando o status correto
            ]
        ]
    ]);

    return json_decode($response->getBody(), true);
}







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
        'range' => '0-499', // Limite de resultados
        
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
        'range' => '0-100',
        'order' => 'DESC',
        'sort' => '15',
    ]);

    if ($response->failed()) {
        throw new \Exception('Erro ao buscar tickets filtrados: ' . $response->body());
    }

    return $response->json();
}




public function getEntities() {
    $response = $this->client->get('Entity', [
        'headers' => [
            'App-Token'    => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->sessionToken,
        ],
    ]);

    return json_decode($response->getBody(), true);
}


public function getUsers() {
    $response = $this->client->get('User', [
        'headers' => [
            'App-Token'    => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->sessionToken,
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



public function getCategories() {
    $response = $this->client->get('ITILCategory', [
        'headers' => [
            'App-Token'    => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->sessionToken,
        ],
    ]);

    return json_decode($response->getBody(), true);
}


}
