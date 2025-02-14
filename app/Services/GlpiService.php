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

public function createTicket($title, $description, $userId, $entityId) {
    $response = $this->client->post('Ticket', [
        'headers' => [
            'App-Token'    => env('GLPI_APP_TOKEN'),
            'Session-Token' => $this->sessionToken,
        ],
        'json' => [
            'input' => [
                'name' => $title,
                'content' => $description,
                'users_id_recipient' => $userId,
                'entities_id' => $entityId
            ]
        ]
    ]);

    return json_decode($response->getBody(), true);
}





    // Buscar um chamado pelo ID
    public function getTicket($ticketId)
{
    $sessionToken = session('glpi_session_token');

    if (!$sessionToken) {
        throw new \Exception('Session Token não encontrado. Inicie a sessão primeiro.');
    }

    $response = Http::withHeaders([
        'App-Token'    => env('GLPI_APP_TOKEN'),
        'Authorization' => 'user_token ' . env('GLPI_USER_TOKEN'),
        'Session-Token' => $sessionToken,
    ])->get(env('GLPI_URL') . "/Ticket/$ticketId");

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



}
