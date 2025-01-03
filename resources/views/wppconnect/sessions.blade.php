@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sessões Ativas do WAPP Connect</h1>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (isset($sessions) && count($sessions) > 0)
        <ul class="list-group">
            @foreach ($sessions as $session)
                <li class="list-group-item">{{ $session }}</li>
            @endforeach
        </ul>
    @else
        <p>Nenhuma sessão ativa encontrada.</p>
    @endif
</div>
@endsection
