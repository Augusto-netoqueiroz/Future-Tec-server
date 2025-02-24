@extends('day.layout')

@section('title', 'Criar Ramal')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Criar Novo Ramal</h1>
    <form action="{{ route('ramais.store') }}" method="POST">
        @csrf

        <!-- Campo Ramal -->
        <div class="mb-3">
            <label for="ramal" class="form-label">Ramal</label>
            <input type="text" class="form-control @error('ramal') is-invalid @enderror" id="ramal" name="ramal" value="{{ old('ramal') }}" required>
            @error('ramal')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

       <!-- Campo Senha -->
            <div class="mb-3">
                <label for="senha" class="form-label">Senha do Ramal</label>
                <div class="input-group">
                    <input type="password" class="form-control @error('senha') is-invalid @enderror" id="senha" name="senha" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleSenha">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('senha')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>


        <!-- Campo Contexto -->
        <div class="mb-3">
            <label for="context" class="form-label">Contexto</label>
            <input type="text" class="form-control @error('context') is-invalid @enderror" id="context" name="context" value="{{ old('context') }}" required>
            @error('context')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Salvar Ramal</button>
    </form>
</div>



<script>
    document.getElementById('toggleSenha').addEventListener('click', function () {
        let senhaInput = document.getElementById('senha');
        let icon = this.querySelector('i');

        if (senhaInput.type === "password") {
            senhaInput.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            senhaInput.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>
@endsection



