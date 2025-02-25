@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Relatório de Login/Logout</h1>

        <!-- Filtros -->
        <form method="GET" action="{{ route('login-report.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="user_name" class="form-control" placeholder="Filtrar por Usuário" value="{{ request('user_name') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="ip_address" class="form-control" placeholder="Filtrar por IP" value="{{ request('ip_address') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Tabela -->
        <table class="table table-bordered table-striped">
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
                      
                        <td>{{ $log->ip_address }}</td>
                        <td>{{ $log->login_time }}</td>
                        <td>{{ $log->logout_time ?? 'Ainda Logado' }}</td>
                        <td>{{ $log->formatted_duration ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Paginação -->
        <div class="d-flex justify-content-center">
            {{ $logs->links() }}
        </div>
    </div>
@endsection

