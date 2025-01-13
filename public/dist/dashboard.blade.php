@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Dashboard</h1>

    <!-- Filtros de data -->
    <form method="GET" action="{{ route('dashboard') }}" class="mb-6">
        <div class="flex items-center space-x-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Data Início</label>
                <input type="datetime-local" id="start_date" name="start_date" value="{{ $start_date }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">Data Fim</label>
                <input type="datetime-local" id="end_date" name="end_date" value="{{ $end_date }}" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    <!-- Análise Geral -->
    <h2 class="text-xl font-bold mt-8 mb-4">Análise Geral</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">TMA</th>
                    <th class="px-4 py-2 border-b">TME</th>
                    <th class="px-4 py-2 border-b">TMO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 py-2 border-b">{{ $generalData[0]->tma ?? '-' }}</td>
                    <td class="px-4 py-2 border-b">{{ $generalData[0]->tme ?? '-' }}</td>
                    <td class="px-4 py-2 border-b">{{ $generalData[0]->tmo ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Análise por Ramal -->
    <h2 class="text-xl font-bold mt-8 mb-4">Análise por Ramal</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">Ramal</th>
                    <th class="px-4 py-2 border-b">Chamadas Atendidas</th>
                    <th class="px-4 py-2 border-b">Chamadas Recusadas</th>
                    <th class="px-4 py-2 border-b">Tempo Total</th>
                    <th class="px-4 py-2 border-b">Tempo Médio</th>
                    <th class="px-4 py-2 border-b">Tempo Médio de Espera</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ramalData as $ramal)
                    <tr>
                        <td class="px-4 py-2 border-b">{{ $ramal->ramal }}</td>
                        <td class="px-4 py-2 border-b">{{ $ramal->chamadas_atendidas }}</td>
                        <td class="px-4 py-2 border-b">{{ $ramal->chamadas_recusadas }}</td>
                        <td class="px-4 py-2 border-b">{{ $ramal->tempo_total_atendimento }}</td>
                        <td class="px-4 py-2 border-b">{{ $ramal->tempo_medio_atendimento }}</td>
                        <td class="px-4 py-2 border-b">{{ $ramal->tempo_medio_espera }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Análise por Fila -->
    <h2 class="text-xl font-bold mt-8 mb-4">Análise por Fila</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">Fila</th>
                    <th class="px-4 py-2 border-b">Chamadas Atendidas</th>
                    <th class="px-4 py-2 border-b">Chamadas Recusadas</th>
                    <th class="px-4 py-2 border-b">Tempo Total</th>
                    <th class="px-4 py-2 border-b">Tempo Médio</th>
                    <th class="px-4 py-2 border-b">Tempo Médio de Espera</th>
                </tr>
            </thead>
            <tbody>
                @foreach($filaData as $fila)
                    <tr>
                        <td class="px-4 py-2 border-b">{{ $fila->fila }}</td>
                        <td class="px-4 py-2 border-b">{{ $fila->chamadas_atendidas }}</td>
                        <td class="px-4 py-2 border-b">{{ $fila->chamadas_recusadas }}</td>
                        <td class="px-4 py-2 border-b">{{ $fila->tempo_total_atendimento }}</td>
                        <td class="px-4 py-2 border-b">{{ $fila->tempo_medio_atendimento }}</td>
                        <td class="px-4 py-2 border-b">{{ $fila->tempo_medio_espera }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Análise por Agentes -->
    <h2 class="text-xl font-bold mt-8 mb-4">Análise por Agentes</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">Agente</th>
                    <th class="px-4 py-2 border-b">Total de Eventos</th>
                    <th class="px-4 py-2 border-b">Atendimentos Realizados</th>
                    <th class="px-4 py-2 border-b">Abandonos</th>
                    <th class="px-4 py-2 border-b">Primeiro Evento</th>
                    <th class="px-4 py-2 border-b">Último Evento</th>
                    <th class="px-4 py-2 border-b">Vinculado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agenteData as $agente)
                    <tr>
                        <td class="px-4 py-2 border-b">{{ $agente->agente ?? 'Desconhecido' }}</td>
                        <td class="px-4 py-2 border-b">{{ $agente->total_eventos }}</td>
                        <td class="px-4 py-2 border-b">{{ $agente->atendimentos_realizados }}</td>
                        <td class="px-4 py-2 border-b">{{ $agente->abandonos }}</td>
                        <td class="px-4 py-2 border-b">{{ $agente->primeiro_evento }}</td>
                        <td class="px-4 py-2 border-b">{{ $agente->ultimo_evento }}</td>
                        <td class="px-4 py-2 border-b">{{ $agente->agente_vinculado ? 'Sim' : 'Não' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
