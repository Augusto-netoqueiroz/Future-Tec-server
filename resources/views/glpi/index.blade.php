@extends('day.layout')

@section('title', 'Criar Ticket')

@section('content')
 

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Ticket - GLPI</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 400px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        input, textarea, select { width: 100%; padding: 10px; margin: 5px 0; }
        button { background: #28a745; color: white; padding: 10px; border: none; cursor: pointer; }
        button:hover { background: #218838; }
    </style>

    <h2 style="text-align: center;">Criar um Novo Ticket</h2>

    @if(session('success'))
    <div style="color: green;">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div style="color: red;">{{ session('error') }}</div>
@endif

<form action="{{ route('glpi.createTicket') }}" method="POST">
    @csrf
    <label for="title">Título:</label>
    <input type="text" name="title" required>

    <label for="description">Descrição:</label>
    <textarea name="description" required></textarea>

    <label for="user_id">ID do Usuário:</label>
    <select name="user_id" class="form-control" required>
                <option value="">Selecione um usuário</option>
                @foreach($users as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                @endforeach
            </select>

  
    <label for="entities_id">Entidade:</label>
    <select name="entities_id" class="form-control" required>
        <option value="">Selecione a Entidade</option>
        @foreach($entities as $entity)
            <option value="{{ $entity['id'] }}">{{ $entity['name'] }}</option>
        @endforeach
    </select>

    <button type="submit">Criar Ticket</button>
</form>


@endsection
