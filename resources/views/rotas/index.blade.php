<!-- rotas/index.blade.php -->

@extends('day.layout')

@section('content')
    <div class="container">
        <h1>Rotas de Discagem</h1>
        
        <!-- Botão para criar nova rota -->
        <a href="{{ route('rotas.create') }}" class="btn btn-primary mb-3">Criar Nova Rota</a>

        <!-- Mensagem de status -->
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <!-- Lista de rotas -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Contexto</th>
                    <th>Discagem</th>
                    <th>Destino</th>
                    <th>Tipo de Discagem</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rotas as $rota)
                    <tr>
                        <td>{{ $rota->context }}</td>
                        <td>{{ $rota->exten }}</td>
                        <td>{{ $rota->appdata }}</td>
                        <td>{{ $rota->tipo_discagem }}</td>
                        <td>
                            <!-- Botão de editar -->
                            <a href="{{ route('rotas.edit', $rota->id) }}" class="btn btn-warning btn-sm">Editar</a>

                            <!-- Formulário de deletar -->
                            <form action="{{ route('rotas.destroy', $rota->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta rota?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
