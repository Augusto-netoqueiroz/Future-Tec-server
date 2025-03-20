<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MagnusBillingService;

class MagnusController extends Controller
{
    protected $magnusBillingService;

    public function __construct(MagnusBillingService $magnusBillingService)
    {
        $this->magnusBillingService = $magnusBillingService;
    }

    public function getBalance(Request $request)
    {
        $username = $request->query('username'); // Obtém o usuário da URL
        
        if (!$username) {
            return response()->json(['error' => 'O parâmetro username é obrigatório'], 400);
        }
    
        $balance = $this->magnusBillingService->getUserBalance($username);
    
        if ($balance !== null) {
            return response()->json(['username' => $username, 'credit' => $balance]);
        }
    
        return response()->json(['error' => 'Usuário não encontrado'], 404);
    }
    

    public function getUsers()
{
    $users = $this->magnusBillingService->getAllUsers();

    if ($users !== null) {
        return response()->json($users);
    }

    return response()->json(['error' => 'Não foi possível obter os usuários'], 500);
}

}    