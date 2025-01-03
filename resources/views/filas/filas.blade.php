@extends('layouts.app')

@section('title', 'Filas')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Gerenciar Filas</h1>
    
    <!-- Botão para redirecionar para a página de criação de fila -->
    <a href="{{ route('filas.create') }}" class="btn btn-success mb-4">Criar Nova Fila</a>

    <!-- Lista de filas -->
    <div class="mt-4">
        <h3>Filas Existentes</h3>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Estratégia</th>
                    <th scope="col">Timeout</th>
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
                        <td>
                            <!-- Link para editar a fila -->
                            <a href="{{ route('filas.edit', $fila->id) }}" class="btn btn-primary">Editar</a>
                            
                            <!-- Link para gerenciar os membros da fila -->
                            <a href="{{ route('filas.manage', $fila->id) }}" class="btn btn-info btn-sm">Gerenciar Membros</a>
                            
                            <!-- Formulário para excluir a fila -->
                            <form action="{{ route('filas.destroy', $fila->id) }}" method="POST" style="display:inline;">
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
