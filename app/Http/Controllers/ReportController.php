<?php

namespace App\Http\Controllers;

use App\Models\LoginReport; // Certifique-se de importar o modelo correto

class ReportController extends Controller
{
    public function index()
    {
        $logs = LoginReport::with('user')->get();
    
        $logs->transform(function ($log) {
            if ($log->session_duration !== null && $log->session_duration >= 0) {
                $duration = $log->session_duration;
                $days = floor($duration / 86400);
                $hours = floor(($duration % 86400) / 3600);
                $minutes = floor(($duration % 3600) / 60);
                $seconds = $duration % 60;
    
                $log->formatted_duration = 
                    ($days > 0 ? "$days dias, " : '') .
                    ($hours > 0 ? "$hours horas, " : '') .
                    ($minutes > 0 ? "$minutes minutos, " : '') .
                    "$seconds segundos";
            } else {
                $log->formatted_duration = 'Duração inválida';
            }
    
            return $log;
        });
    
        return view('login-report.index', compact('logs'));
    }
    
}
