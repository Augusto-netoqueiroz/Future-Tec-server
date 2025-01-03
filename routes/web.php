<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallEventController;
use App\Http\Controllers\RamalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PainelAtendimentoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\FilaController;
use App\Http\Controllers\RotasController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\WppConnectController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CampaignController;
use \App\Http\Middleware\CheckPermission;
use App\Http\Controllers\DashboardController;

// Rota principal
Route::get('/', function () {
    return Auth::check() 
        ? redirect()->route('home') 
        : redirect()->route('login');
})->name('root');

// Rota home
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Exibe a página de login
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');

// USER
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::delete('/users/{id}/logout', [UserController::class, 'logoutUser'])->name('users.logoutUser');
Route::resource('users', UserController::class);
Route::post('users/{id}/logout', [UserController::class, 'logoutUser'])->name('users.logoutUser');
Route::post('/users/{id}/logout', [UserController::class, 'logoutUser'])->name('users.logoutUser');


// Permissões
Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

Route::post('permissions', [PermissionController::class, 'update'])->name('permissions.update');

// Processa o login
Route::post('/login', [UserController::class, 'login'])->name('user.login');

// Logout
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Outras rotas públicas
Route::get('/call-events', [CallEventController::class, 'index'])->name('call-events');
Route::get('/consulta-estado', [RamalController::class, 'consultarEstado']);

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// Painel de atendimento
Route::get('/painel-atendimento', [PainelAtendimentoController::class, 'index'])->name('painel-atendimento');
Route::post('/associar-ramal', [PainelAtendimentoController::class, 'associar'])->name('associar-ramal');
Route::post('/desassociar-ramal', [PainelAtendimentoController::class, 'desassociar'])->name('desassociar-ramal');
Route::delete('/desassociar-ramal', [PainelAtendimentoController::class, 'desassociar'])->name('desassociar-ramal');

// Página de ramais telefonia
Route::prefix('ramais')->name('ramais.')->group(function () {
    Route::get('/', [RamalController::class, 'index'])->name('index');
    Route::get('create', [RamalController::class, 'create'])->name('create');
    Route::post('store', [RamalController::class, 'store'])->name('store');
    Route::get('{ramal}/edit', [RamalController::class, 'edit'])->name('edit');
    Route::put('{ramal}', [RamalController::class, 'update'])->name('update');
    Route::delete('{ramal}', [RamalController::class, 'destroy'])->name('destroy');
    Route::put('{ramal}', [RamalController::class, 'update'])->name('ramais.update');

});

// Relatórios
Route::get('/relatorios/ligacoes', [RelatorioController::class, 'ligacoes'])->name('relatorios.ligacoes');
Route::get('/relatorios/login', [ReportController::class, 'index'])->name('login-report.index');

// Filas
Route::prefix('filas')->name('filas.')->group(function () {
    Route::get('/', [FilaController::class, 'index'])->name('index');
    Route::get('create', [FilaController::class, 'create'])->name('create');
    Route::post('store', [FilaController::class, 'store'])->name('store');
    Route::get('/filas/{id}/edit', [FilaController::class, 'edit'])->name('filas.edit');
    Route::put('update/{id}', [FilaController::class, 'update'])->name('update');
    Route::delete('destroy/{id}', [FilaController::class, 'destroy'])->name('destroy');
    Route::get('{id}/gerenciar', [FilaController::class, 'manageMembers'])->name('manage');
    Route::post('{id}/associar', [FilaController::class, 'associateMember'])->name('associateMember');
    Route::delete('{queueId}/membros/{userId}', [FilaController::class, 'removeMember'])->name('removeMember');
    Route::post('{fila}/member/{member}/update', [FilaController::class, 'updateMemberState'])->name('updateMemberState');
});
Route::get('/filas/{id}/edit', [FilaController::class, 'edit'])->name('filas.edit');



// Rota para atualizar os dados da fila
Route::put('/filas/{id}', [FilaController::class, 'update'])->name('filas.update');

// Agentes
Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');
Route::delete('/agents/{id}', [AgentController::class, 'destroy'])->name('agents.destroy');
Route::post('/agents/pause', [AgentController::class, 'pauseAgent'])->name('agents.pause');
Route::get('/agents/test-pause', [AgentController::class, 'pauseTest'])->name('agents.test-pause');
Route::post('/ami', [AgentController::class, 'pauseAgent']);

// Rotas
Route::resource('rotas', RotasController::class);

// WppConnect
Route::prefix('wppconnect')->name('wppconnect.')->group(function () {
    Route::get('/', [WppConnectController::class, 'index'])->name('index');
    Route::post('start-session', [WppConnectController::class, 'startSession'])->name('start-session');
    Route::get('sessions', [WppConnectController::class, 'showAllSessions'])->name('sessions');
});

// Troncos
Route::prefix('troncos')->name('troncos.')->group(function () {
    Route::get('/', [RamalController::class, 'listarTroncos'])->name('index');
    Route::get('create', [RamalController::class, 'criarTronco'])->name('create');
    Route::post('/', [RamalController::class, 'salvarTronco'])->name('store');
    Route::get('{id}/edit', [RamalController::class, 'editarTronco'])->name('edit');
    Route::put('{id}', [RamalController::class, 'atualizarTronco'])->name('update');
});



// Campanhas
Route::prefix('campaigns')->name('campaigns.')->group(function () {
    Route::get('/', [CampaignController::class, 'index'])->name('index');
    Route::get('create', [CampaignController::class, 'create'])->name('create');
    Route::post('/', [CampaignController::class, 'store'])->name('store');
    Route::get('{id}/upload', [CampaignController::class, 'uploadContacts'])->name('uploadContacts');
    Route::post('{id}/upload', [CampaignController::class, 'storeContacts'])->name('storeContacts');
});

Route::get('/teste', function () {
    return 'Teste';
});

//DASHBOARD
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

//Verificação de sessão
Route::group(['middleware' => ['auth', 'verify.session']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Outras rotas protegidas...
});