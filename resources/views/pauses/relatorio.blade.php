@extends('day.layout')

@section('content')
<div class="container mt-4">
    <h1 class="text-center mb-4">Relatório de Pausas</h1>

    <!-- Filtro -->
    <div class="card p-4 shadow-sm">
        <form id="filter-form">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Usuário</label>
                    <select name="user_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Filtrar</button>
        </form>
    </div>

    <!-- Resumo -->
    <h2 class="mt-5 text-primary text-center">Resumo de Pausas</h2>
    <table class="table table-bordered mt-3 text-center" id="resumo-table">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Tempo Total Disponível</th>
                <th>Tempo Total em Pausa</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Extrato -->
    <h2 class="mt-5">Extrato de Pausas</h2>
    <table class="table table-striped mt-3" id="logs-table">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Pausa</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Duração</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Paginação -->
    <div id="pagination-container" class="d-flex justify-content-center mt-4"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filter-form');
    const resumoTable = document.querySelector('#resumo-table tbody');
    const logsTable = document.querySelector('#logs-table tbody');
    const paginationContainer = document.getElementById('pagination-container');

    async function fetchData(pageUrl = "{{ route('relatorio.pausas.filtrar') }}") {
        const formData = new FormData(form);

        const response = await fetch(pageUrl, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                'Accept': 'application/json' 
            },
            body: formData,
        });

        const data = await response.json();
        atualizarExtrato(data.logs);
        paginationContainer.innerHTML = data.pagination;
    }

    function atualizarExtrato(logs) {
        logsTable.innerHTML = logs.map(row => `
            <tr>
                <td>${row.user_name}</td>
                <td>${row.pause_name}</td>
                <td>${row.started_at}</td>
                <td>${row.end_at || '-'}</td>
                <td>${formatTime(row.duration_seconds)}</td>
            </tr>
        `).join('');
    }

    function formatTime(seconds) {
        if (seconds < 0 || isNaN(seconds)) return '-';
        const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
        const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        return `${h}:${m}:${s}`;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchData();
    });

    paginationContainer.addEventListener('click', function(e) {
        e.preventDefault();
        if (e.target.tagName === 'A') {
            fetchData(e.target.href);
        }
    });

    fetchData(); // Carrega os dados iniciais
});
</script>

@endsection
