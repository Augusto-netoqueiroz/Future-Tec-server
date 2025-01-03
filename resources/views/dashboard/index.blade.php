{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Resumo Geral</h1>
    <p><strong>TMA (Tempo Médio de Atendimento):</strong> {{ number_format($generalData[0]->tma ?? 0, 2) }} segundos</p>
    <p><strong>TME (Tempo Médio de Espera):</strong> {{ number_format($generalData[0]->tme ?? 0, 2) }} segundos</p>
    <p><strong>TMO (Tempo Médio Total):</strong> {{ number_format($generalData[0]->tmo ?? 0, 2) }} segundos</p>

    <h2>Análise por Ramal</h2>
    <table>
        <thead>
            <tr>
                <th>Ramal</th>
                <th>Chamadas Atendidas</th>
                <th>Chamadas Recusadas</th>
                <th>Tempo Total de Atendimento</th>
                <th>Tempo Médio de Atendimento</th>
                <th>Tempo Médio de Espera</th>
                <th>Total de Eventos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ramalData as $ramal)
                <tr>
                    <td>{{ $ramal->ramal }}</td>
                    <td>{{ $ramal->chamadas_atendidas }}</td>
                    <td>{{ $ramal->chamadas_recusadas }}</td>
                    <td>{{ gmdate('H:i:s', $ramal->tempo_total_atendimento ?? 0) }}</td>
                    <td>{{ gmdate('H:i:s', $ramal->tempo_medio_atendimento ?? 0) }}</td>
                    <td>{{ gmdate('H:i:s', $ramal->tempo_medio_espera ?? 0) }}</td>
                    <td>{{ $ramal->total_eventos }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Análise por Filas</h2>
    <table>
        <thead>
            <tr>
                <th>Fila</th>
                <th>Chamadas Atendidas</th>
                <th>Chamadas Recusadas</th>
                <th>Tempo Total de Atendimento</th>
                <th>Tempo Médio de Atendimento</th>
                <th>Tempo Médio de Espera</th>
                <th>Total de Eventos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filaData as $fila)
                    <tr>
                        <td>{{ $fila->fila }}</td>
                        <td>{{ $fila->chamadas_atendidas }}</td>
                        <td>{{ $fila->chamadas_recusadas }}</td>
                        <td>{{ gmdate('H:i:s', $fila->tempo_total_atendimento ?? 0) }}</td>
                        <td>{{ gmdate('H:i:s', $fila->tempo_medio_atendimento ?? 0) }}</td>
                        <td>{{ gmdate('H:i:s', $fila->tempo_medio_espera ?? 0) }}</td>
                    </tr>
            @endforeach

        </tbody>
    </table>
@endsection
