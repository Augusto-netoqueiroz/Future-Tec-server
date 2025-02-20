@extends('day.index')

@section('title', 'Gerenciar Filas')

@section('content')
<div class="container-xxl py-8">
    <!-- Título da página -->
    <h1 class="fs-2 fw-bold text-center mb-6">Gerenciar Filas</h1>
    
    <!-- Botão para criar uma nova fila -->
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('filas.create') }}" class="btn btn-success">Criar Nova Fila</a>
    </div>

    <!-- Tabela de filas existentes -->
    <h2 class="fs-4 fw-bold mb-4">Filas Existentes</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
    <tr>
        <th scope="col">ID</th>
        <th scope="col">Nome</th>
        <th scope="col">Estratégia</th>
        <th scope="col">Timeout</th>
        <th scope="col">Empresa</th> <!-- Nova coluna -->
        <th scope="col">Ações</th>
    </tr>
</thead>
<tbody>
    @foreach($filas as $fila)
        <tr>
            <td>{{ $fila->id }}</td>
            <td>{{ $fila->name }}</td>
            <td>{{ $fila->strategy }}</td>
            <td>{{ $fila->timeout }}</td>
            <td>{{ $fila->empresa_id }} - {{ Auth::user()->empresa->nome }}</td> <!-- Exibir Empresa ID -->
            <td class="d-flex gap-2">
                <a href="{{ route('filas.edit', $fila->id) }}" class="btn btn-primary btn-sm">Editar</a>
                <a href="{{ route('filas.manage', $fila->id) }}" class="btn btn-info btn-sm">Membros</a>
                <form action="{{ route('filas.destroy', $fila->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta fila?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                </form>
            </td>
        </tr>
    @endforeach
</tbody>

        </table>
    </div>
</div>
@endsection
