@extends('day.layout')

@section('content')
<div class="container">
    <h1>Editar Ramal</h1>

    <form action="{{ route('ramais.update', $ramal->id) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Ramal</label>
            <input type="text" name="name" id="name" value="{{ $ramal->name }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="secret">Senha</label>
            <input type="text" name="secret" id="secret" value="{{ $ramal->secret }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="context">Contexto</label>
            <input type="text" name="context" id="context" value="{{ $ramal->context }}" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>
</div>
@endsection
