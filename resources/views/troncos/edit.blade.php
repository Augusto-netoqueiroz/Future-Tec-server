@extends('layouts.app')

@section('title', 'Editar Tronco')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Editar Tronco</h1>

    <form method="POST" action="{{ route('troncos.update', $tronco->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Tronco</label>
            <input type="text" class="form-control" id="nome" name="nome" value="{{ $tronco->name }}" required>
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" class="form-control" id="senha" name="senha" value="{{ $tronco->secret }}" required>
        </div>
        <div class="mb-3">
            <label for="context" class="form-label">Contexto</label>
            <input type="text" class="form-control" id="context" name="context" value="{{ $tronco->context }}" required>
        </div>
        <div class="mb-3">
            <label for="host" class="form-label">Host</label>
            <input type="text" class="form-control" id="host" name="host" value="{{ $tronco->host }}" required>
        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('troncos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
