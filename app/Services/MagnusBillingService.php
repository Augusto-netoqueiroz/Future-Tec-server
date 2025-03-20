<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MagnusBillingService
{
    protected $baseUrl;
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->baseUrl = env('MAGNUS_API_URL'); // URL do Magnus
        $this->apiKey = env('MAGNUS_API_KEY'); // API Key
        $this->apiSecret = env('MAGNUS_API_SECRET'); // Secret Key
    }

    public function getUserBalance($username)
    {
        $url = "{$this->baseUrl}/api/user";

        // Configuração do filtro corretamente
        $data = [
            'filter' => [
                ['field' => 'username', 'value' => $username, 'comparison' => 'eq', 'type' => 'string']
            ]
        ];

        Log::info("Enviando requisição para Magnus Billing:", ['url' => $url, 'data' => $data]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->post($url, $data); // Importante usar POST!

        Log::info("Resposta Magnus Billing:", ['status' => $response->status(), 'body' => $response->body()]);

        if ($response->successful()) {
            $result = $response->json();

            if (!empty($result['records']) && isset($result['records'][0]['credit'])) {
                return $result['records'][0]['credit'];
            }

            Log::warning("Usuário '$username' não encontrado no Magnus.");
        } else {
            Log::error("Erro ao consultar API Magnus Billing: " . $response->status());
        }

        return null;
    }


    public function getAllUsers()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->get("{$this->baseUrl}/api/users");
    
        if ($response->successful()) {
            return $response->json(); // Retorna a lista de usuários
        }
    
        return null;
    }

}
