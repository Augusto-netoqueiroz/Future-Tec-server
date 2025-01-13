<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserPauseLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }


    public function login(Request $request)
    {
        // Validação dos dados de login
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        try {
            // Autentica o usuário
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
    
                // Criação do registro de pausa com ID 6
                $pause = Pause::findOrFail(6);  // Pausa com ID 6
                $log = UserPauseLog::create([
                    'user_id' => $user->id,
                    'pause_id' => $pause->id,
                    'pause_name' => $pause->name,
                    'pause_start' => now(),
                    'started_at' => now(),
                ]);
    
                // Atualiza o usuário com o estado da pausa
                $user->update([
                    'pause' => $pause->name,
                    'current_pause_log_id' => $log->id,
                ]);
    
                // Atualiza o estado da pausa na tabela queue_members
                DB::table('queue_members')
                    ->where('user_id', $user->id)
                    ->update(['paused' => 1]);
    
                // Redireciona ou retorna um sucesso
                return redirect()->route('dashboard')->with('success', 'Login realizado com sucesso e pausa iniciada!');
            } else {
                return back()->withErrors(['login_error' => 'Credenciais inválidas']);
            }
        } catch (\Exception $e) {
            Log::error('Erro no login: ' . $e->getMessage());
            return back()->withErrors(['login_error' => 'Erro ao tentar fazer login.']);
        }
    }


}




