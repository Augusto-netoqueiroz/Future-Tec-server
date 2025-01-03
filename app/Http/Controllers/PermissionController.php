<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        // Obter o cargo do usuário logado
        $userCargo = auth()->user()->cargo;

        // Definir os cargos que têm permissão
        $cargosPermitidos = ['Administrador', 'Supervisor'];

        // Verificar se o cargo do usuário está na lista de cargos permitidos
        if (!in_array($userCargo, $cargosPermitidos)) {
            // Caso não tenha permissão, retorna o erro 403
            abort(403, 'Você não tem permissão para acessar esta página.');
        }

        // Carregar módulos e rotas do arquivo de configuração
        $modules = Config::get('modules');

        // Carregar permissões existentes no banco, agrupadas por cargo
        $permissions = Permission::all()->groupBy('cargo');

        // Definir os cargos manualmente (opcional se for necessário na view)
        $cargos = ['administrador', 'supervisor', 'operador'];

        return view('permissions.index', compact('modules', 'permissions', 'cargos'));
    }

    public function update(Request $request)
    {
        // Certifica-se de que o campo 'permissions' exista para evitar erros de null
        if (!$request->has('permissions')) {
            return redirect()->back()->with('error', 'Nenhuma permissão foi enviada.');
        }

        foreach ($request->permissions as $cargo => $routes) {
            foreach ($routes as $route => $allowed) {
                // Determinar o módulo (se necessário)
                $module = $this->getModuleForRoute($route);

                // Verificar ou criar uma permissão para o cargo e a rota
                $permissionModel = Permission::firstOrNew([
                    'cargo' => $cargo,
                    'page' => $route,
                ]);

                // Atualizar os valores
                $permissionModel->module = $module;
                $permissionModel->allowed = (int) $allowed; // Converte o valor para inteiro (0 ou 1)
                $permissionModel->save();
            }
        }

        return redirect()->back()->with('success', 'Permissões atualizadas com sucesso!');
    }

    // Método para determinar o módulo com base na rota (caso seja necessário em outro ponto)
    protected function getModuleForRoute($route)
    {
        $modules = [
            'home' => 'Home',
            'users.index' => 'Usuários',
            'permissions.index' => 'Permissões',
            'painel-atendimento' => 'Painel de Atendimento',
            // Adicione outros mapeamentos de rota para módulo aqui
        ];

        return $modules[$route] ?? 'DefaultModule'; // Retorna um módulo padrão, caso não encontre
    }
}
