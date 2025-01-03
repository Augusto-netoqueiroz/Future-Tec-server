@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Criar Tronco</h1>
        <form method="POST" action="{{ route('troncos.store') }}">
            @csrf
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Tronco</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="mb-3">
                <label for="context" class="form-label">Contexto</label>
                <input type="text" class="form-control" id="context" name="context" required>
            </div>
            <div class="mb-3">
                <label for="host" class="form-label">Host</label>
                <input type="text" class="form-control" id="host" name="host" required>
            </div>
            <button type="submit" class="btn btn-success">Salvar</button>
        </form>
    </div>
@endsection
