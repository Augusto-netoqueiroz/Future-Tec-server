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
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->user->name }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->login_time }}</td>
                        <td>{{ $log->logout_time ?? 'Ainda Logado' }}</td>
                        <td>{{ $log->session_duration ? $log->session_duration . ' segundos' : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
