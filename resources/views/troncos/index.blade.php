@extends('layouts.app')

@section('title', 'Lista de Troncos')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Lista de Troncos</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('troncos.create') }}" class="btn btn-primary">Novo Tronco</a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tronco</th>
                <th>Estado</th>
                <th>Host</th>
                <th>Contexto</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($troncos as $tronco)
                <tr>
                    <td>{{ $tronco->id }}</td>
                    <td>{{ $tronco->tronco }}</td>
                    <td>
                        <span class="badge 
                            @if($tronco->estado == 'Online') 
                                bg-success 
                            @else 
                                bg-danger 
                            @endif
                        ">
                            {{ $tronco->estado }}
                        </span>
                    </td>
                    <td>{{ $tronco->host }}</td>
                    <td>{{ $tronco->context }}</td>
                    <td>
                        <a href="{{ route('troncos.edit', $tronco->id) }}" class="btn btn-warning btn-sm">Editar</a>

                        <!-- Formulário de Deletar -->
                        <form action="{{ route('ramais.destroy', $tronco->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja deletar este tronco?');">
                                Deletar
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
