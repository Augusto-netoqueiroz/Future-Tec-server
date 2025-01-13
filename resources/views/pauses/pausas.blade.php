@extends('day.layout')

@section('title', 'Gerenciar Pausas')

@section('content')
<div class="container mt-5">
    <h1 class="fw-bold text-primary mb-4">Gerenciar Pausas</h1>

    {{-- Exibe mensagens de sucesso ou erro --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-4">
            <i class="ki-duotone ki-check fs-2x text-success me-4"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Botão para adicionar nova pausa --}}
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('pauses.create') }}" class="btn btn-primary">Adicionar Nova Pausa</a>
    </div>

    {{-- Tabela de pausas --}}
    <div class="card card-flush">
        <div class="card-body pt-0">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead class="table-light text-start fw-bold">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pauses as $pause)
                        <tr>
                            <td>{{ $pause->id }}</td>
                            <td>{{ $pause->name }}</td>
                            <td class="text-end">
                                <a href="{{ route('pauses.edit', $pause->id) }}" class="btn btn-warning btn-sm me-2">
                                    <i class="ki-duotone ki-pencil"></i> Editar
                                </a>
                                <form action="{{ route('pauses.destroy', $pause->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta pausa?');">
                                        <i class="ki-duotone ki-trash"></i> Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
