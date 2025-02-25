<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\LoginReport; // Certifique-se de importar o modelo correto

class ReportController extends Controller
{public function index(Request $request)
    {

        $users = DB::table('users')->select('id', 'name')->orderBy('name')->get();

        $query = DB::table('login_logs')
            ->join('users', 'login_logs.user_id', '=', 'users.id')
            ->select(
                'login_logs.id',
                'users.name as user_name',
                'login_logs.ip_address',
                'login_logs.login_time',
                'login_logs.logout_time',
                DB::raw('ABS(login_logs.session_duration) as session_duration')
            )
            ->orderByDesc('login_logs.login_time'); // Ordena do mais novo para o mais antigo
    
        // Aplicação de filtros
        if ($request->has('user_name')) {
            $query->where('users.name', 'like', '%' . $request->user_name . '%');
        }
    
        if ($request->has('ip_address')) {
            $query->where('login_logs.ip_address', 'like', '%' . $request->ip_address . '%');
        }
    
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('login_logs.login_time', [$request->date_from, $request->date_to]);
        }
    
        // Paginação
        $logs = $query->paginate(50);
    
        return view('login-report.index', compact('logs', 'users'));
    }
    
    
}
