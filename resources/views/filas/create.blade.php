@extends('day.layout')

@section('title', 'Criar Fila')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Criar Nova Fila</h1>

    <!-- Formulário para criar fila -->
    <form action="{{ route('filas.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="id" class="form-label">ID da Fila</label>
            <input type="number" class="form-control" id="id" name="id" required>
            <small class="form-text text-muted">Escolha um ID único para a fila.</small>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Nome da Fila</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="strategy" class="form-label">Estratégia</label>
            <select class="form-control" id="strategy" name="strategy" required>
                <option value="leastrecent">Leastrecent</option>
                <option value="random">Random</option>
                <option value="rrmemory">Rrmemory</option>
                <option value="ringall">Ringall</option>
                <option value="leastcall">Leastcall</option>
                <option value="randomized">Randomized</option>
                <option value="linear">Linear</option>
                <option value="fixed">Fixed</option>
                <option value="ringallv2">Ringallv2</option>
                <option value="roundrobin">Roundrobin</option>
                <option value="priority">Priority</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="timeout" class="form-label">Timeout</label>
            <input type="number" class="form-control" id="timeout" name="timeout" required>
        </div>

        <!-- Novos campos -->
        <div class="mb-3">
            <label for="musiconhold" class="form-label">Música de Espera</label>
            <input type="text" class="form-control" id="musiconhold" name="musiconhold">
            <small class="form-text text-muted">Classe de música de espera para a fila.</small>
        </div>

        <div class="mb-3">
            <label for="announce_frequency" class="form-label">Frequência do Anúncio (segundos)</label>
            <input type="number" class="form-control" id="announce_frequency" name="announce_frequency">
            <small class="form-text text-muted">Intervalo em segundos para repetir os anúncios.</small>
        </div>

        <div class="mb-3">
            <label for="servicelevel" class="form-label">Nível de Serviço (segundos)</label>
            <input type="number" class="form-control" id="servicelevel" name="servicelevel">
            <small class="form-text text-muted">Tempo para calcular o SLA da fila.</small>
        </div>

        <div class="mb-3">
            <label for="wrapuptime" class="form-label">Tempo de Descanso (segundos)</label>
            <input type="number" class="form-control" id="wrapuptime" name="wrapuptime">
            <small class="form-text text-muted">Tempo de descanso após uma chamada atendida.</small>
        </div>

        <div class="mb-3">
            <label for="joinempty" class="form-label">Permitir Entrada na Fila Vazia</label>
            <select class="form-control" id="joinempty" name="joinempty">
                <option value="yes">Sim</option>
                <option value="no">Não</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="leavewhenempty" class="form-label">Sair da Fila Quando Vazia</label>
            <select class="form-control" id="leavewhenempty" name="leavewhenempty">
                <option value="yes">Sim</option>
                <option value="no">Não</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="ringinuse" class="form-label">Permitir Chamadas para Membros Ocupados</label>
            <select class="form-control" id="ringinuse" name="ringinuse">
                <option value="1">Sim</option>
                <option value="0">Não</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Fila</button>
        <a href="{{ route('filas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
