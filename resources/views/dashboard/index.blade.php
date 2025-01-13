@extends('day.layout')

<!--begin::Content container-->
@section('content')
<div class="container-xxl py-8">
    <!-- Filtros de data -->
    <form method="GET" action="{{ route('dashboard') }}" class="mb-6">
        <div class="row g-4">
            <div class="col-md-5">
                <label for="start_date" class="form-label">Data Início</label>
                <input type="datetime-local" id="start_date" name="start_date" value="{{ $start_date }}" 
                       class="form-control">
            </div>
            <div class="col-md-5">
                <label for="end_date" class="form-label">Data Fim</label>
                <input type="datetime-local" id="end_date" name="end_date" value="{{ $end_date }}" 
                       class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    <!-- Análise Geral -->
    <h2 class="fs-2 fw-bold mt-8 mb-4">Análise Geral</h2>
    <div class="row">
        <div class="col-md-6">
            <canvas id="chartTMA"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="chartTMO"></canvas>
        </div>
    </div>

    <!-- Análise por Ramal -->
    <h2 class="fs-2 fw-bold mt-8 mb-4">Análise por Ramal</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Ramal</th>
                    <th>Chamadas Atendidas</th>
                    <th>Chamadas Recusadas</th>
                    <th>Tempo Total</th>
                    <th>Tempo Médio</th>
                    <th>Tempo Médio de Espera</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ramalData as $ramal)
                    <tr>
                        <td>{{ $ramal->ramal }}</td>
                        <td>{{ $ramal->chamadas_atendidas }}</td>
                        <td>{{ $ramal->chamadas_recusadas }}</td>
                        <td>{{ $ramal->tempo_total_atendimento }}</td>
                        <td>{{ $ramal->tempo_medio_atendimento }}</td>
                        <td>{{ $ramal->tempo_medio_espera }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Gráfico de Análise por Ramal -->
    <div class="mt-8">
        <canvas id="chartRamal"></canvas>
    </div>

    <!-- Análise por Fila -->
    <h2 class="fs-2 fw-bold mt-8 mb-4">Análise por Fila</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Fila</th>
                    <th>Chamadas Atendidas</th>
                    <th>Chamadas Recusadas</th>
                    <th>Tempo Total</th>
                    <th>Tempo Médio</th>
                    <th>Tempo Médio de Espera</th>
                </tr>
            </thead>
            <tbody>
                @foreach($filaData as $fila)
                    <tr>
                        <td>{{ $fila->fila }}</td>
                        <td>{{ $fila->chamadas_atendidas }}</td>
                        <td>{{ $fila->chamadas_recusadas }}</td>
                        <td>{{ $fila->tempo_total_atendimento }}</td>
                        <td>{{ $fila->tempo_medio_atendimento }}</td>
                        <td>{{ $fila->tempo_medio_espera }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Gráfico de Análise por Fila -->
    <div class="mt-8">
        <canvas id="chartFila"></canvas>
    </div>
</div>
@endsection

@section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="assets/plugins/custom/fullcalendar/fullcalendar.bundle.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/radar.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/map.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/geodata/worldLow.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/geodata/continentsLow.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/geodata/usaLow.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZonesLow.js"></script>
		<script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZoneAreasLow.js"></script>
		<script src="assets/plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    // Gráfico de TMA, TME, TMO
    const ctxTMA = document.getElementById('chartTMA').getContext('2d');
    const chartTMA = new Chart(ctxTMA, {
        type: 'bar',
        data: {
            labels: ['TMA', 'TME', 'TMO'],
            datasets: [{
                label: 'Tempo Médio',
                data: [{{ $generalData[0]->tma ?? 0 }}, {{ $generalData[0]->tme ?? 0 }}, {{ $generalData[0]->tmo ?? 0 }}],
                backgroundColor: ['#007bff', '#28a745', '#ffc107'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Tempo Médio por Categoria' }
            }
        }
    });

    // Gráfico de Chamadas por Ramal
    const ctxRamal = document.getElementById('chartRamal').getContext('2d');
    const chartRamal = new Chart(ctxRamal, {
        type: 'pie',
        data: {
            labels: @json($ramalData->pluck('ramal')),
            datasets: [{
                label: 'Chamadas Atendidas',
                data: @json($ramalData->pluck('chamadas_atendidas')),
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Chamadas Atendidas por Ramal' }
            }
        }
    });

    // Gráfico de Chamadas por Fila
    const ctxFila = document.getElementById('chartFila').getContext('2d');
    const chartFila = new Chart(ctxFila, {
        type: 'bar',
        data: {
            labels: @json($filaData->pluck('fila')),
            datasets: [{
                label: 'Chamadas Atendidas',
                data: @json($filaData->pluck('chamadas_atendidas')),
                backgroundColor: '#007bff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Chamadas Atendidas por Fila' }
            }
        }
    });
</script>
@endsection
