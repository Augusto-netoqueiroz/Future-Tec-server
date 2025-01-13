@extends('day.layout')

@section('title', 'Criar Nova Pausa')

@section('content')
<div class="container mt-5">
    <h1 class="fw-bold text-primary mb-4">Criar Nova Pausa</h1>

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

    {{-- Formulário para criação de pausa --}}
    <form action="{{ route('pauses.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nome da Pausa</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Digite o nome da pausa" required>
        </div>

        <button type="submit" class="btn btn-primary">Criar Pausa</button>
        <a href="{{ route('Pausas.inicio') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
