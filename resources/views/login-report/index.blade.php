@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Relatório de Login/Logout</h1>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>IP</th>
                    <th>Login</th>
                    <th>Logout</th>
                    <th>Duração</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td>{{ $log->user->name }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->login_time }}</td>
                        <td>{{ $log->logout_time ?? 'Ainda Logado' }}</td>
                        <td>
                            @if ($log->session_duration)
                                @php
                                    $duration = $log->session_duration; // Em segundos
                                    $days = floor($duration / 86400);
                                    $hours = floor(($duration % 86400) / 3600);
                                    $minutes = floor(($duration % 3600) / 60);
                                    $seconds = $duration % 60;
                                @endphp
            
                                @if ($duration < 0)
                                    Duração inválida
                                @else
                                    {{ $days > 0 ? $days . ' dias, ' : '' }}
                                    {{ $hours > 0 ? $hours . ' horas, ' : '' }}
                                    {{ $minutes > 0 ? $minutes . ' minutos, ' : '' }}
                                    {{ $seconds . ' segundos' }}
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>            
        </table>
    </div>
@endsection
