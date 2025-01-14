@extends('day.layout')

@section('content')
<div class="container">
    <h1 class="mb-4 text-center">Relatório de Pausas</h1>

    <form id="filter-form" method="POST" action="{{ route('relatorio.pausas.filtrar') }}" class="mb-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Data Início</label>
                <input type="date" id="start_date" name="start_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">Data Fim</label>
                <input type="date" id="end_date" name="end_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="user_id" class="form-label">Usuário</label>
                <select id="user_id" name="user_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Filtrar</button>
    </form>

    <div class="row">
        <div class="col-md-6 mb-4">
            <canvas id="summaryChart"></canvas>
        </div>
        <div class="col-md-6 mb-4">
            <canvas id="availabilityChart"></canvas>
        </div>
    </div>

    <h2 class="mt-5 text-center text-primary">Resumo de Pausas</h2>
    <table class="table table-hover border rounded shadow-sm" id="resumo-table">
        <thead class="table-light">
            <tr>
                <th>Usuário</th>
                <th>Tempo Total Disponível</th>
                <th>Tempo Total em Pausa</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dados preenchidos via JavaScript -->
        </tbody>
    </table>

    <h2 class="mt-5">Extrato de Pausas</h2>
    <table class="table table-bordered" id="logs-table">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Pausa</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Duração</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dados preenchidos via JavaScript -->
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css"></script>
<script>
document.getElementById('filter-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);

    const response = await fetch(e.target.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
        },
        body: formData,
    });

    const data = await response.json();

    // Atualizar tabela de resumo
    const resumoTable = document.querySelector('#resumo-table tbody');
    resumoTable.innerHTML = '';
    data.resumo.forEach(row => {
        resumoTable.innerHTML += `<tr>
            <td>${row.user_name}</td>
            <td>${formatTime(row.total_disponivel)}</td>
            <td>${formatTime(row.total_pausa)}</td>
        </tr>`;
    });

    // Atualizar tabela de extrato com DataTables
    const logsTable = $('#logs-table').DataTable();
    logsTable.clear();
    data.logs.forEach(row => {
        logsTable.row.add([
            row.user_name,
            row.pause_name,
            row.started_at,
            row.end_at,
            formatTime(row.duration_seconds),
        ]);
    });
    logsTable.draw();

    renderCharts(data.logs, data.resumo);
});

function formatTime(seconds) {
    const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
    const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
    const s = (seconds % 60).toString().padStart(2, '0');
    return `${h}:${m}:${s}`;
}

function renderCharts(logs, resumo) {
    const userPauseData = {};

    logs.forEach(log => {
        if (log.pause_name !== 'disponível') {
            if (!userPauseData[log.user_name]) {
                userPauseData[log.user_name] = {};
            }
            userPauseData[log.user_name][log.pause_name] = 
                (userPauseData[log.user_name][log.pause_name] || 0) + log.duration_seconds;
        }
    });

    const labels = Object.keys(userPauseData);
    const pauseTypes = [...new Set(logs.filter(log => log.pause_name !== 'disponível').map(log => log.pause_name))];
    const datasets = pauseTypes.map((pauseType, index) => ({
        label: pauseType,
        data: labels.map(user => userPauseData[user][pauseType] || 0),
        backgroundColor: `hsl(${index * 60}, 70%, 50%)`,
    }));

    // Gráfico de Barras (sem "disponível")
    new Chart(document.getElementById('summaryChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
        },
    });

    // Gráfico de Pizza (Doughnut) - Disponível x Pausa
    const totalDisponivel = resumo.reduce((sum, row) => sum + row.total_disponivel, 0);
    const totalPausa = resumo.reduce((sum, row) => sum + row.total_pausa, 0);

    new Chart(document.getElementById('availabilityChart'), {
        type: 'doughnut',
        data: {
            labels: ['Disponível', 'Em Pausa'],
            datasets: [
                {
                    data: [totalDisponivel, totalPausa],
                    backgroundColor: ['#4caf50', '#f44336'],
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
        },
    });
}

// Inicializar DataTables para a tabela de extrato
$(document).ready(function() {
    $('#logs-table').DataTable({
        paging: true,
        searching: true,
        responsive: true,
        lengthChange: false,
        pageLength: 10,
        order: [[2, 'desc']]
    });
});
</script>
@endsection
