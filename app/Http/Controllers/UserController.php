<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
                return redirect()->route('home');
            } elseif ($this->isMd5($user->password) && md5($request->password) === $user->password) {
                $user->password = Hash::make($request->password);
                $user->save();

                Auth::login($user);
                return redirect()->route('home');
            }

            return back()->withErrors(['login_error' => 'Credenciais inválidas']);
        }

        return back()->withErrors(['login_error' => 'Credenciais inválidas']);
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

            // Limpar a coluna `interface` na tabela `queue_members`
            DB::table('queue_members')
                ->where('user_id', $user->id)
                ->update(['interface' => null]);

            DB::table('agente_ramal_vinculo')
                ->where('agente_id', $user->id)
                ->whereNull('fim_vinculo')
                ->update(['fim_vinculo' => Carbon::now()]);

                 // Limpeza do SIP e logout
            DB::table('sippeers')
                ->where('user_id', $user->id)
                ->orWhere('user_name', $user->name)
                ->update(['user_id' => null, 'user_name' => null]);

            session()->flush();
            Auth::logout();

            return redirect()->route('login');
        }

        return redirect()->route('login');
    }

    public function logoutUser($id)
    {
        try {
            DB::table('sessions')->where('user_id', $id)->delete();

            DB::table('agente_ramal_vinculo')
                ->where('agente_id', $id)
                ->whereNull('fim_vinculo')
                ->update(['fim_vinculo' => now()]);

            DB::table('queue_members')
                ->where('user_id', $id)
                ->update(['interface' => null]);

            $user = User::find($id);
            if ($user && Auth::id() == $id) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
            }

            if (Auth::id() == $id) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect()->route('login')->with('success', 'Sessão encerrada com sucesso.');
            }

            return redirect()->route('users.index')->with('success', 'Sessão encerrada com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Erro ao encerrar a sessão: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $users = User::all();

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
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'cargo' => $request->cargo,
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
        ]);

        try {
            $user = User::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->cargo = $request->cargo;

            if ($request->password) {
                $user->password = bcrypt($request->password);
            }

            $user->save();
            return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocorreu um erro ao atualizar o usuário. Tente novamente.');
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            DB::table('sessions')->where('user_id', $id)->delete();

            $user->delete();
            return redirect()->route('users.index')->with('success', 'Usuário deletado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao tentar deletar o usuário. Tente novamente.');
        }
    }
}
