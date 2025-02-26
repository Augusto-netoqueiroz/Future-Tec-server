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
        // Obter o usuário pelo ID
        $user = User::find($id);

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

            // Remover a sessão do usuário pelo ID
            DB::table('sessions')->where('user_id', $id)->delete();

            // Registrar atividade
            DB::table('atividade')->insert([
                'user_id'   => Auth::id(),
                'acao'      => 'Logout de Usuário',
                'descricao' => "Usuário " . Auth::user()->name . " encerrou a sessão de $user->name.",
                'ip'        => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            

            // Se o usuário encerrado for o mesmo autenticado, desconecta e redireciona para o login
            if (Auth::id() == $id) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect()->route('login')->with('success', 'Sua sessão foi encerrada.');
            }
        }

        return redirect()->route('users.index')->with('success', 'Sessão encerrada com sucesso.');
    } catch (\Exception $e) {
        return redirect()->route('users.index')->with('error', 'Erro ao encerrar a sessão: ' . $e->getMessage());
    }
}



public function logoutsistema($id)
{
    try {
        // Obter o usuário pelo ID
        $user = User::find($id);

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

            // Remover a sessão do usuário pelo ID
            DB::table('sessions')->where('user_id', $id)->delete();

            

            // Registrar atividade quando o sistema finaliza a sessão automaticamente
            DB::table('atividade')->insert([
                'user_id'   => $user->id,
                'acao'      => 'Logout Automático',
                'descricao' => "Sistema finalizou automaticamente a sessão do usuário $user->name.",
                'ip'        => '127.0.0.1', // IP padrão para ações do sistema
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Se o usuário encerrado for o mesmo autenticado, desconecta e redireciona para o login
            if (Auth::id() == $id) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect()->route('login')->with('success', 'Sua sessão foi encerrada.');
            }
        }

        return redirect()->route('users.index')->with('success', 'Sessão encerrada com sucesso.');
    } catch (\Exception $e) {
        return redirect()->route('users.index')->with('error', 'Erro ao encerrar a sessão: ' . $e->getMessage());
    }
}


    


    public function index()
    {

        if (!Auth::check()) {
            return redirect()->route('login'); // Redireciona para a tela de login se não estiver autenticado
        }
        
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

    $novoUsuario = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'cargo' => $request->cargo,
        'avatar' => $avatarPath,
        'empresa_id' => $user->empresa_id, 
        'empresa_nome' => $user->empresa_nome, // Garante que o novo usuário pertence à mesma empresa
    ]);

    // Registrar atividade
    DB::table('atividade')->insert([
        'user_id'   => $user->id,
        'acao'      => 'Criação de Usuário',
        'descricao' => "Usuário " . $user->name . " criou o usuário " . $novoUsuario->name . " (ID: " . $novoUsuario->id . ").",
        'ip'        => request()->ip(),
        'created_at' => now(),
        'updated_at' => now(),
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
    $authUser = auth()->user(); // Usuário autenticado

    // Capturar mudanças
    $changes = [];

    if ($request->name !== $user->name) {
        $changes[] = "Nome: '{$user->name}' → '{$request->name}'";
    }
    if ($request->email !== $user->email) {
        $changes[] = "Email: '{$user->email}' → '{$request->email}'";
    }
    if ($request->cargo !== $user->cargo) {
        $changes[] = "Cargo: '{$user->cargo}' → '{$request->cargo}'";
    }
    if (!empty($request->password)) { 
        $changes[] = "Senha alterada"; 
    }
    if ($request->hasFile('avatar')) {
        $changes[] = "Avatar atualizado";
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->avatar = $request->file('avatar')->store('avatars', 'public');
    }

    // Atualizar os dados do usuário
    $updateData = [
        'name' => $request->name,
        'email' => $request->email,
        'cargo' => $request->cargo,
        'avatar' => $user->avatar,
    ];

    if (!empty($request->password)) {
        $updateData['password'] = bcrypt($request->password);
    }

    $user->update($updateData);

    // Se houver alterações, registrar atividade
    if (!empty($changes)) {
        DB::table('atividade')->insert([
            'user_id'   => $authUser->id,
            'acao'      => 'Atualização de Usuário',
            'descricao' => "Usuário " . $authUser->name . " alterou o usuário " . $user->name . " (ID: " . $user->id . "). Alterações: " . implode(', ', $changes),
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
}


public function destroy($id)
{
    try {
        $user = User::findOrFail($id);
        $authUser = auth()->user(); // Usuário autenticado

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        DB::table('sessions')->where('user_id', $id)->delete();
        $user->delete();

        // Registrar atividade
        DB::table('atividade')->insert([
            'user_id'   => $authUser->id,
            'acao'      => 'Exclusão de Usuário',
            'descricao' => "Usuário " . $authUser->name . " deletou o usuário " . $user->name . " (ID: " . $user->id . ").",
            'ip'        => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
    $authUser = auth()->user(); // Usuário autenticado
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


    // Registrar atividade
    DB::table('atividade')->insert([
        'user_id'   => $authUser->id,
        'acao'      => 'Criação de empresa',
        'descricao' => "Usuário " . $authUser->name . " criou a empresa -> " . '(' . $request->nome . ')',
        'ip'        => request()->ip(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('empresas.create')->with('success', 'Empresa criada com sucesso!');
}


}
