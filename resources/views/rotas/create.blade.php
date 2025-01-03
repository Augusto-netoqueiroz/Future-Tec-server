<!-- rotas/create.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Criar Nova Rota</h1>
        
        <!-- Formulário para criar uma nova rota -->
        <form action="{{ route('rotas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="contexto_discagem">Contexto de Discagem</label>
                <input type="text" name="contexto_discagem" id="contexto_discagem" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="discagem">Discagem</label>
                <input type="text" name="discagem" id="discagem" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="destino">Destino</label>
                <input type="text" name="destino" id="destino" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="tipo_discagem">Tipo de Discagem</label>
                <select name="tipo_discagem" id="tipo_discagem" class="form-control" required>
                    <option value="ramal">Ramal</option>
                    <option value="fila">Fila</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success mt-3">Criar Rota</button>
        </form>
    </div>
@endsection
