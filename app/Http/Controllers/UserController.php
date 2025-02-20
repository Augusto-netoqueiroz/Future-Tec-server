<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\UserPauseLog;
use App\Models\Pause;

class UserController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user) {
        if ($this->isBcrypt($user->password) && Hash::check($request->password, $user->password)) {
            Auth::login($user);

            // Criar o registro na tabela user_pause_logs
            $this->createPauseLog($user->id, 6); // ID da pausa fixado como 6
            
            return redirect()->route('home');
        } elseif ($this->isMd5($user->password) && md5($request->password) === $user->password) {
            $user->password = Hash::make($request->password);
            $user->save();

            Auth::login($user);

            // Criar o registro na tabela user_pause_logs
            $this->createPauseLog($user->id, 6); // ID da pausa fixado como 6
            
            return redirect()->route('home');
        }

        return back()->withErrors(['login_error' => 'Credenciais inválidas']);
    }

    return back()->withErrors(['login_error' => 'Credenciais inválidas']);
}

/**
 * Cria um registro na tabela user_pause_logs.
 *
 * @param int $userId
 * @param int $pauseId
 */
/**
 * Cria um registro de pausa para o usuário.
 *
 * @param int $userId
 * @param int $pauseId
 */


 private function createPauseLog($userId, $pauseId = 6) // Pausa padrão "Disponível"
{
    try {
        $pause = Pause::findOrFail($pauseId);

        $log = UserPauseLog::create([
            'user_id' => $userId,
            'pause_id' => $pause->id,
            'started_at' => now(),
        ]);

        if ($log) {
            $user = User::findOrFail($userId);
            $user->update([
                'pause' => $pause->name,
                'current_pause_log_id' => $log->id,
            ]);
        }
    } catch (\Exception $e) {
        Log::error("Erro ao criar pausa: {$e->getMessage()}");
    }
}




    private function isBcrypt($hash)
    {
        return preg_match('/^\$2y\$/', $hash);
    }

    private function isMd5($hash)
    {
        return strlen($hash) === 32 && ctype_xdigit($hash);
    }

    public function logout(Request $request)
{
    $user = Auth::user();

    if ($user) {
        // Atualiza o último log de pausa do usuário, preenchendo a coluna end_at
        $currentPauseLogId = $user->current_pause_log_id;
        if ($currentPauseLogId) {
            DB::table('user_pause_logs')
                ->where('id', $currentPauseLogId)
                ->whereNull('end_at')
                ->update(['end_at' => now()]);
        }

        // Atualiza o log de login
        $loginLog = DB::table('login_logs')
            ->where('user_id', $user->id)
            ->whereNull('logout_time')
            ->orderBy('login_time', 'desc')
            ->first();

        if ($loginLog) {
            $loginTime = Carbon::parse($loginLog->login_time);
            $logoutTime = Carbon::now();
            $sessionDuration = $loginTime->diffInSeconds($logoutTime);

            DB::table('login_logs')
                ->where('id', $loginLog->id)
                ->update([
                    'logout_time' => $logoutTime,
                    'session_duration' => $sessionDuration,
                ]);
        }

        // Limpa o vínculo do ramal
        DB::table('queue_members')->where('user_id', $user->id)->update(['interface' => null]);

        DB::table('agente_ramal_vinculo')
            ->where('agente_id', $user->id)
            ->whereNull('fim_vinculo')
            ->update(['fim_vinculo' => Carbon::now()]);

        DB::table('sippeers')
            ->where('user_id', $user->id)
            ->orWhere('user_name', $user->name)
            ->update(['user_id' => null, 'user_name' => null]);

        // Atualiza o usuário para remover a pausa atual e o ID do log de pausa
        DB::table('users')
            ->where('id', $user->id)
            ->update(['pause' => null, 'current_pause_log_id' => null]);

        session()->flush();
        Auth::logout();

        return redirect()->route('login');
    }

    return redirect()->route('login');
}


    public function logoutUser($id)
    {
        try {
            // Remover a sessão do usuário pelo ID
            DB::table('sessions')->where('user_id', $id)->delete();
    
            // Atualizar a tabela 'agente_ramal_vinculo', encerrando o vínculo
            DB::table('agente_ramal_vinculo')
                ->where('agente_id', $id)
                ->whereNull('fim_vinculo')
                ->update(['fim_vinculo' => now()]);
    
            // Atualizar a tabela 'queue_members', removendo o interface
            DB::table('queue_members')->where('user_id', $id)->update(['interface' => null]);
    
            // Obter o usuário pelo ID
            $user = User::find($id);
    
            // Se o usuário encerrado for o mesmo autenticado, desconecta e redireciona para o login
            if ($user && Auth::id() == $id) {
                Auth::logout(); // Desconectar o usuário atual
                session()->invalidate(); // Invalidar a sessão
                session()->regenerateToken(); // Regenerar o token CSRF
    
                return redirect()->route('login')->with('success', 'Sua sessão foi encerrada.');
            }
    
            return redirect()->route('users.index')->with('success', 'Sessão encerrada com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Erro ao encerrar a sessão: ' . $e->getMessage());
        }
    }
    


    public function index()
    {
        $user = auth()->user(); // Obtém o usuário autenticado
    
        $users = User::where('empresa_id', $user->empresa_id)->get(); // Filtra apenas os usuários da mesma empresa
    
        $users->map(function ($user) {
            $session = DB::table('sessions')->where('user_id', $user->id)->first();
            $user->is_online = $session ? true : false;
            $user->ip_address = $session ? $session->ip_address : null;
            return $user;
        });
    
        return view('users.index', compact('users'));
    }
    

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'cargo' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);
    
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }
    
        $user = auth()->user(); // Obtém o usuário autenticado
    
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'cargo' => $request->cargo,
            'avatar' => $avatarPath,
            'empresa_id' => $user->empresa_id, 
            'empresa_nome' => $user->empresa_nome,// Garante que o novo usuário pertence à mesma empresa
        ]);
    
        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }
    

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'cargo' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $user = User::findOrFail($id);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'cargo' => $request->cargo,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'avatar' => $user->avatar,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            DB::table('sessions')->where('user_id', $id)->delete();

            $user->delete();

            return redirect()->route('users.index')->with('success', 'Usuário deletado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao tentar deletar o usuário. Tente novamente.');
        }
    }


    public function togglePause(Request $request)
{
    $pauseId = $request->input('pause_id');
    
    // Lógica para aplicar a pausa
    $user = auth()->user();
    $pause = Pause::find($pauseId);

    if ($pause) {
        // Aplique a pausa ao usuário
        $user->update(['pause_id' => $pause->id]);
        return redirect()->back()->with('success', 'Você entrou em pausa.');
    }

    return redirect()->back()->with('error', 'Pausa não encontrada.');
}
    

public function createEmpresa()
{
    return view('empresas.create');
}

public function storeEmpresa(Request $request)
{
    $request->validate([
        'nome' => 'required|string|max:255',
        'cnpj' => 'required|string|max:255|unique:empresas,cnpj',
    ]);

    DB::table('empresas')->insert([
        'nome' => $request->nome,
        'cnpj' => $request->cnpj,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('empresas.create')->with('success', 'Empresa criada com sucesso!');
}


}
