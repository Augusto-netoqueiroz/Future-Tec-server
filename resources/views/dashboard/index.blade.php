<!-- resources/views/dashboard/index.blade.php -->
@extends('day.layout')

@section('content')
<div class="container">
    <h1 class="my-4">Dashboard</h1>

    <!-- Filtros -->
    <form action="{{ route('dashboard.index') }}" method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-2">
                <label for="user_id">Usuário</label>
                <select name="user_id" id="user_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="ramal_id">Ramal</label>
                <select name="ramal_id" id="ramal_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($ramais as $ramal)
                        <option value="{{ $ramal->id }}" {{ request('ramal_id') == $ramal->id ? 'selected' : '' }}>
                            {{ $ramal->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="queue_name">Fila</label>
                <select name="queue_name" id="queue_name" class="form-control">
                    <option value="">Todas</option>
                    @foreach($filas as $fila)
                        <option value="{{ $fila->name }}" {{ request('queue_name') == $fila->name ? 'selected' : '' }}>
                            {{ $fila->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="start_date">Data Início</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label for="end_date">Data Fim</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Resumo -->
    <div class="mb-4">
        <h3>Resumo</h3>
        <table class="table table-bordered">
            <tr>
                <th>Recebidas</th>
                <th>Perdidas</th>
                <th>Efetuadas</th>
                <th>Tempo Médio Atendimento</th>
                <th>Tempo Médio Ocupação</th>
                <th>SLA (%)</th>
                <th>Nível de Serviço (%)</th>
            </tr>
            <tr>
                <td>{{ $resumo['recebidas'] }}</td>
                <td>{{ $resumo['perdidas'] }}</td>
                <td>{{ $resumo['efetuadas'] }}</td>
                <td>{{ $resumo['tempo_medio_atendimento'] }} seg</td>
                <td>{{ $resumo['tempo_medio_ocupacao'] }} seg</td>
                <td>{{ $resumo['sla'] }}</td>
                <td>{{ $resumo['nivel_servico'] }}</td>
            </tr>
        </table>
        <p class="text-muted">* O resumo é baseado nos filtros aplicados.</p>
    </div>

    <!-- Vínculos -->
    <div class="mb-4">
        <h3>Vínculos de Agentes</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Ramal</th>
                    <th>Início</th>
                    <th>Fim</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vinculos as $vinculo)
                    <tr>
                        <td>{{ $vinculo->user->name }}</td>
                        <td>{{ $vinculo->sippeer->name }}</td>
                        <td>{{ $vinculo->inicio_vinculo }}</td>
                        <td>{{ $vinculo->fim_vinculo }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Logs da Fila -->
    <div>
        <h3>Logs da Fila</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Fila</th>
                    <th>Evento</th>
                    <th>Duração</th>
                    <th>Horário</th>
                </tr>
            </thead>
            <tbody>
                @foreach($queueLogs as $log)
                    <tr>
                        <td>{{ $log->queuename }}</td>
                        <td>{{ $log->event }}</td>
                        <td>{{ $log->duration ?? 'N/A' }}</td>
                        <td>{{ $log->time }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
