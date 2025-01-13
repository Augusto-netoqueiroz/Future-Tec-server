@extends('day.layout')

@section('title', 'Editar Pausa')

@section('content')
<div class="container mt-5">
    <h1 class="fw-bold text-primary mb-4">Editar Pausa</h1>

    {{-- Exibe mensagens de validação --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulário para edição de pausa --}}
    <form action="{{ route('pauses.update', $pause->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nome da Pausa</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $pause->name }}" required>
        </div>

        <button type="submit" class="btn btn-warning">Salvar Alterações</button>
        <a href="{{ route('Pausas.inicio') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
