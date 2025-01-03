@extends('layouts.app')

@section('title', 'Painel de Atendimento')

@section('content')
    <h1>Bem-vindo ao Painel de Atendimento</h1>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <h3>Ramais Disponíveis para Associações</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Ramal</th>
                <th>Status</th>
                <th>Atendente</th> <!-- Nova coluna para mostrar o nome do atendente -->
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ramaisOnline as $ramal)
                <tr>
                    <td>{{ $ramal->name }}</td>
                    <td>
                        @if ($ramal->user_name) <!-- Verifica se há um atendente associado -->
                            Associado
                        @else
                            Livre
                        @endif
                    </td>
                    <td>
                        @if ($ramal->user_name) <!-- Exibe o nome do atendente se o ramal estiver associado -->
                            {{ $ramal->user_name }}
                        @else
                            Nenhum atendente
                        @endif
                    </td>
                    <td>
                        @if (!$ramal->user_name) <!-- Botão para associar apenas se o ramal não estiver associado -->
                            <form action="{{ route('associar-ramal') }}" method="POST">
                                @csrf
                                <input type="hidden" name="ramal_id" value="{{ $ramal->id }}">
                                <button type="submit" class="btn btn-primary">Associar a mim</button>
                            </form>
                        @else
                            <form action="{{ route('desassociar-ramal') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Desassociar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
