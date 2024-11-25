<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallEventController;
use App\Http\Controllers\RamalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PainelAtendimentoController;
use App\Http\Controllers\RelatorioController;

// Rota principal protegida
Route::get('/', function () {
    return Auth::check() 
        ? redirect()->route('home') 
        : redirect()->route('login');
})->name('root');

// Rota home protegida (exige autenticação)
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

// Exibe a página de login (somente para visitantes)
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login')->middleware('guest');

// Processa o login
Route::post('/login', [UserController::class, 'login'])->name('user.login');

// Logout (protege a rota com middleware 'auth')
Route::post('/logout', [UserController::class, 'logout'])->name('logout')->middleware('auth');

// Outras rotas públicas
//Route::get('/call-events', [CallEventController::class, 'index']);
Route::get('/call-events', [CallEventController::class, 'index'])->name('call-events'); // Aqui você adiciona o name
Route::get('/consulta-estado', [RamalController::class, 'consultarEstado']);



Route::prefix('admin')->middleware('auth')->group(function () {
    // Página para listar os usuários
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    
    // Página para criar um novo usuário
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    
    // Página para editar um usuário existente
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    
    // Rota para armazenar um novo usuário
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    
    // Rota para atualizar um usuário existente
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    
    // Rota para excluir um usuário
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    
});

Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');

//Painel de atendimento
Route::get('/painel-atendimento', [PainelAtendimentoController::class, 'index'])->name('painel-atendimento');
Route::post('/associar-ramal', [PainelAtendimentoController::class, 'associar'])->name('associar-ramal');
Route::post('/desassociar-ramal', [PainelAtendimentoController::class, 'desassociar'])->name('desassociar-ramal');
Route::delete('/desassociar-ramal', [PainelAtendimentoController::class, 'desassociar'])->name('desassociar-ramal');


//Pagina de ramais telefonia inicio
Route::get('/ramais', [RamalController::class, 'index'])->name('ramais.index');
Route::post('/ramais', [RamalController::class, 'store'])->name('ramais.store');
Route::delete('/ramais/{id}', [RamalController::class, 'destroy'])->name('ramais.destroy');

Route::prefix('ramais')->name('ramais.')->group(function () {
    Route::get('/', [RamalController::class, 'index'])->name('index');       // Página de listagem de ramais
    Route::get('create', [RamalController::class, 'create'])->name('create'); // Página de criação de ramal
    Route::post('store', [RamalController::class, 'store'])->name('store');    // Para salvar o novo ramal
    Route::get('{ramal}/edit', [RamalController::class, 'edit'])->name('edit'); // Página de edição
    Route::put('{ramal}', [RamalController::class, 'update'])->name('update');  // Para atualizar o ramal
    Route::delete('{ramal}', [RamalController::class, 'destroy'])->name('destroy'); // Para excluir o ramal
});

//Pagina de ramais telefonia Fim

// relatórios
Route::get('/relatorios/ligacoes', [RelatorioController::class, 'ligacoes'])->name('relatorios.ligacoes');