@extends('day.layout')

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
            <div class="mb-3">
                <label for="context" class="form-label">Contexto</label>
                <input type="text" class="form-control" id="context" name="context" required>
            </div>
            <div class="mb-3">
                <label for="host" class="form-label">Host</label>
                <input type="text" class="form-control" id="host" name="host">
            </div>
            <button type="submit" class="btn btn-success">Salvar</button>
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
