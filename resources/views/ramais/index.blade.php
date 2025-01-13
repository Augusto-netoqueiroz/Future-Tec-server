@extends('day.layout')

@section('title', 'Lista de Ramais')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Lista de Ramais</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('ramais.create') }}" class="btn btn-primary">Novo Ramal</a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Ramal</th>
                <th>Estado</th>
                <th>IP</th>
                <th>Contexto</th> 
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ramais as $ramal)
                <tr>
                    <td>{{ $ramal->id }}</td>
                    <td>{{ $ramal->ramal }}</td>
                    <td>
                        <span class="badge 
                            @if($ramal->estado == 'Online') 
                                bg-success 
                            @else 
                                bg-danger 
                            @endif
                        ">
                            {{ $ramal->estado }}
                        </span>
                    </td>
                    <td>{{ $ramal->ipaddr }}</td>
                    <td>{{ $ramal->context }}</td> 
                    <td>
                        <a href="{{ route('ramais.edit', $ramal->id) }}" class="btn btn-warning btn-sm">Editar</a>

                        <!-- Formulário de Deletar -->
                        <form action="{{ route('ramais.destroy', $ramal->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja deletar este ramal?');">
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