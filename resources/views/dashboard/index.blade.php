@extends('day.layout')

@section('content')
<div class="container">
    <h1>Dashboard de Chamadas</h1>

    <!-- Filtros -->
    <div class="mb-4">
        <form id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label for="userFilter" class="form-label">Usuário</label>
                <select id="userFilter" class="form-select">
                    <option value="">Todos</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="startDate" class="form-label">Data Inicial</label>
                <input type="date" id="startDate" class="form-control" value="{{ request('start_date', now()->toDateString()) }}">
            </div>
            <div class="col-md-3">
                <label for="endDate" class="form-label">Data Final</label>
                <input type="date" id="endDate" class="form-control" value="{{ request('end_date', now()->toDateString()) }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100" onclick="fetchFilteredData()">Filtrar</button>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-secondary w-100" onclick="resetFilter()">Limpar</button>
            </div>
        </form>
    </div>

    <!-- Tabela de Resumo de Chamadas -->
    <h3>Resumo de Chamadas</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Atendidas</th>
                <th>Perdidas</th>
                <th>Efetuadas</th>
            </tr>
        </thead>
        <tbody id="callsSummary">
            @foreach($resumo as $item)
                <tr>
                    <td>{{ $item['usuario'] }}</td>
                    <td>{{ $item['recebidas'] }}</td>
                    <td>{{ $item['perdidas'] }}</td>
                    <td>{{ $item['efetuadas'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

   <!-- Tabela de Resumo das Filas -->
<h3>Resumo das Filas</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Fila</th>
            <th>Recebidas</th>
            <th>Atendidas</th>
            <th>Abandonadas</th>
            <th>SLA (%)</th>
        </tr>
    </thead>
    <tbody id="queuesSummary">
        @foreach($resumoFilas as $fila)
            <tr>
                <td>{{ $fila->fila }}</td>
                <td>{{ $fila->total_recebidas }}</td>
                <td>{{ $fila->atendidas }}</td>
                <td>{{ $fila->abandonadas }}</td>
                <td>{{ number_format($fila->sla, 2) }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

<script>
    function fetchFilteredData() {
        let userId = document.getElementById('userFilter').value;
        let startDate = document.getElementById('startDate').value;
        let endDate = document.getElementById('endDate').value;
        let url = new URL(window.location.href);

        url.searchParams.set('user_id', userId);
        url.searchParams.set('start_date', startDate);
        url.searchParams.set('end_date', endDate);

        window.location.href = url.toString();
    }

    function resetFilter() {
        document.getElementById('userFilter').value = "";
        document.getElementById('startDate').value = "{{ now()->toDateString() }}";
        document.getElementById('endDate').value = "{{ now()->toDateString() }}";
        fetchFilteredData();
    }
</script>

@endsection
