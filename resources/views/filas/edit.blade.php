@extends('layouts.app')

@section('title', 'Editar Fila')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Editar Fila</h1>

    <!-- Formulário para editar fila -->
    <form action="{{ route('filas.update', $fila->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="id" class="form-label">ID da Fila</label>
            <input type="number" class="form-control" id="id" name="id" value="{{ $fila->id }}" disabled>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Nome da Fila</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $fila->name }}" required>
        </div>
        <div class="mb-3">
            <label for="strategy" class="form-label">Estratégia</label>
            <select class="form-control" id="strategy" name="strategy" required>
                <option value="leastrecent" {{ $fila->strategy == 'leastrecent' ? 'selected' : '' }}>Leastrecent</option>
                <option value="random" {{ $fila->strategy == 'random' ? 'selected' : '' }}>Random</option>
                <option value="rrmemory" {{ $fila->strategy == 'rrmemory' ? 'selected' : '' }}>Rrmemory</option>
                <option value="ringall" {{ $fila->strategy == 'ringall' ? 'selected' : '' }}>Ringall</option>
                <option value="leastcall" {{ $fila->strategy == 'leastcall' ? 'selected' : '' }}>Leastcall</option>
                <option value="randomized" {{ $fila->strategy == 'randomized' ? 'selected' : '' }}>Randomized</option>
                <option value="linear" {{ $fila->strategy == 'linear' ? 'selected' : '' }}>Linear</option>
                <option value="fixed" {{ $fila->strategy == 'fixed' ? 'selected' : '' }}>Fixed</option>
                <option value="ringallv2" {{ $fila->strategy == 'ringallv2' ? 'selected' : '' }}>Ringallv2</option>
                <option value="roundrobin" {{ $fila->strategy == 'roundrobin' ? 'selected' : '' }}>Roundrobin</option>
                <option value="priority" {{ $fila->strategy == 'priority' ? 'selected' : '' }}>Priority</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="timeout" class="form-label">Timeout</label>
            <input type="number" class="form-control" id="timeout" name="timeout" value="{{ $fila->timeout }}" required>
        </div>

        <!-- Novos campos -->
        <div class="mb-3">
            <label for="musiconhold" class="form-label">Música de Espera</label>
            <input type="text" class="form-control" id="musiconhold" name="musiconhold" value="{{ $fila->musiconhold }}">
        </div>
        <div class="mb-3">
            <label for="announce_frequency" class="form-label">Frequência do Anúncio (segundos)</label>
            <input type="number" class="form-control" id="announce_frequency" name="announce_frequency" value="{{ $fila->announce_frequency }}">
        </div>
        <div class="mb-3">
            <label for="servicelevel" class="form-label">Nível de Serviço (segundos)</label>
            <input type="number" class="form-control" id="servicelevel" name="servicelevel" value="{{ $fila->servicelevel }}">
        </div>
        <div class="mb-3">
            <label for="wrapuptime" class="form-label">Tempo de Descanso (segundos)</label>
            <input type="number" class="form-control" id="wrapuptime" name="wrapuptime" value="{{ $fila->wrapuptime }}">
        </div>
        <div class="mb-3">
            <label for="joinempty" class="form-label">Permitir Entrada na Fila Vazia</label>
            <select class="form-control" id="joinempty" name="joinempty">
                <option value="yes" {{ $fila->joinempty == 'yes' ? 'selected' : '' }}>Sim</option>
                <option value="no" {{ $fila->joinempty == 'no' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="leavewhenempty" class="form-label">Sair da Fila Quando Vazia</label>
            <select class="form-control" id="leavewhenempty" name="leavewhenempty">
                <option value="yes" {{ $fila->leavewhenempty == 'yes' ? 'selected' : '' }}>Sim</option>
                <option value="no" {{ $fila->leavewhenempty == 'no' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="ringinuse" class="form-label">Permitir Chamadas para Membros Ocupados</label>
            <select class="form-control" id="ringinuse" name="ringinuse">
                <option value="1" {{ $fila->ringinuse == 1 ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ $fila->ringinuse == 0 ? 'selected' : '' }}>Não</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="{{ route('filas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
