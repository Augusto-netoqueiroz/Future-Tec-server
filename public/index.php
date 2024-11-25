<?php

// Carregar o autoload do Composer
require __DIR__.'/../vendor/autoload.php';

// Iniciar a aplicação Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// Executar a requisição e retornar a resposta
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Enviar a resposta para o navegador
$response->send();

// Fechar a requisição
$kernel->terminate($request, $response);

