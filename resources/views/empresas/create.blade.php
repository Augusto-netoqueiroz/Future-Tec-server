@extends('day.layout')

@section('content')
<div class="container">
    <h2>Criar Nova Empresa</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('empresas.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nome" class="form-label">Nome da Empresa</label>
            <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome') }}" required>
            @error('nome')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="cnpj" class="form-label">CNPJ</label>
            <input type="text" class="form-control @error('cnpj') is-invalid @enderror" id="cnpj" name="cnpj" value="{{ old('cnpj') }}" required>
            @error('cnpj')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Empresa (somente leitura) --}}
        <div class="mb-3">
            <label for="empresa" class="form-label">Empresa</label>
            <input type="text" class="form-control" id="empresa" value="{{ auth()->user()->empresa_id }}" disabled>
        </div>


        <button type="submit" class="btn btn-primary">Criar Empresa</button>
    </form>
</div>
@endsection
