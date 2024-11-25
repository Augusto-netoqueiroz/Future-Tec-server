<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // Importação do Auth
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // Exibe a página de login
    public function showLoginForm()
    {
        return view('login');
    }

    // Autentica o usuário
    public function login(Request $request)
    {
        // Validação da requisição
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Busca o usuário no banco de dados
        $user = User::where('email', $request->email)->first();

        // Verifica se o usuário existe
        if ($user) {
            // Verifica o formato do hash e valida a senha
            if ($this->isBcrypt($user->password)) {
                if (Hash::check($request->password, $user->password)) {
                    Auth::login($user); // Define o usuário autenticado
                    return redirect()->route('home'); // Redireciona para a rota home
                }
            } elseif ($this->isMd5($user->password)) {
                // Se for MD5, valida e converte para bcrypt
                if (md5($request->password) === $user->password) {
                    $user->password = Hash::make($request->password);
                    $user->save();

                    session(['user' => $user]);
                    return redirect()->route('home');
                }
            }

            // Se não for válido em nenhum formato
            return back()->withErrors(['login_error' => 'Credenciais inválidas']);
        }

        // Se o usuário não for encontrado
        return back()->withErrors(['login_error' => 'Credenciais inválidas']);
    }

    // Função para verificar se a senha está no formato bcrypt
    private function isBcrypt($hash)
    {
        return preg_match('/^\$2y\$/', $hash);
    }

    // Função para verificar se a senha está no formato MD5
    private function isMd5($hash)
    {
        return strlen($hash) === 32 && ctype_xdigit($hash);
    }

    // Logout
    
    public function logout(Request $request)
{
    // Obtém o usuário autenticado
    $user = Auth::user();

    // Verifica se o usuário está autenticado
    if ($user) {
        // Desassocia o ramal do usuário, removendo user_id e user_name
        DB::table('sippeers')
            ->where('user_id', $user->id)
            ->orWhere('user_name', $user->name) // Adiciona a verificação pelo user_name
            ->update([
                'user_id' => null,
                'user_name' => null, // Remove também o user_name
            ]);
    }

    // Limpa a sessão
    session()->flush();

    // Faz o logout do usuário
    Auth::logout();

    // Redireciona para a página de login
    return redirect()->route('login');
}


    public function index()
    {
        $users = User::all();  // ou qualquer lógica que você precise
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create'); // Crie a view 'users.create' para o formulário
    }

    // Método para armazenar o novo usuário no banco de dados
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit($id)
{
    // Busca o usuário pelo ID
    $user = User::findOrFail($id);

    // Retorna a view com os dados do usuário para edição
    return view('users.edit', compact('user'));
}

public function update(Request $request, $id)
{
    // Validação dos dados recebidos
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'password' => 'nullable|string|min:8|confirmed',
    ]);

    try {
        // Busca o usuário pelo ID
        $user = User::findOrFail($id);

        // Atualiza os dados do usuário
        $user->name = $request->name;
        $user->email = $request->email;

        // Se a senha foi informada, atualiza
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        // Mensagem de sucesso
        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    } catch (\Exception $e) {
        // Em caso de erro, você pode capturar a exceção e exibir uma mensagem de erro
        return back()->with('error', 'Ocorreu um erro ao atualizar o usuário. Tente novamente.');
    }
}


}
