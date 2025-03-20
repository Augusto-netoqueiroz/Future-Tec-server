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
use App\Http\Controllers\PauseController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\Liguetalkcontroller;
use App\Http\Controllers\GlpiController;
use \App\Http\Middleware\TesteMiddleware;
use App\Http\Controllers\MagnusController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return view('login');
})->name('root');

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');

// USER
//Route::get('/users', [UserController::class, 'index'])->name('users.index');
//Route::delete('/users/{id}/logout', [UserController::class, 'logoutUser'])->name('users.logoutUser');
Route::resource('users', UserController::class);
Route::post('users/{id}/logout', [UserController::class, 'logoutUser'])->name('users.logoutUser');

Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
Route::post('permissions', [PermissionController::class, 'update'])->name('permissions.update');

Route::post('/login', [UserController::class, 'login'])->name('user.login');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/call-events', [CallEventController::class, 'index'])->name('call-events');
Route::get('/consulta-estado', [RamalController::class, 'consultarEstado']);

// Admin Routes
//Route::prefix('admin')->group(function () {
    //Route::get('users', [UserController::class, 'index'])->name('users.index');
    //Route::get('users/create', [UserController::class, 'create'])->name('users.create');
   // Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    //Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
    //::post('users', [UserController::class, 'store'])->name('users.store');
    //Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    //Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
//});

Route::get('/painel-atendimento', [PainelAtendimentoController::class, 'index'])->name('painel-atendimento');
Route::post('/associar-ramal', [PainelAtendimentoController::class, 'associar'])->name('associar-ramal');
Route::post('/desassociar-ramal', [PainelAtendimentoController::class, 'desassociar'])->name('desassociar-ramal');
//Route::delete('/desassociar-ramal', [PainelAtendimentoController::class, 'desassociar'])->name('desassociar-ramal');

Route::prefix('ramais')->name('ramais.')->group(function () {
    Route::get('/', [RamalController::class, 'index'])->name('index');
    Route::get('create', [RamalController::class, 'create'])->name('create');
    Route::post('store', [RamalController::class, 'store'])->name('store');
    Route::get('{ramal}/edit', [RamalController::class, 'edit'])->name('edit');
    Route::put('{ramal}', [RamalController::class, 'update'])->name('update');
    Route::delete('{ramal}', [RamalController::class, 'destroy'])->name('destroy');
});

Route::get('/relatorios/ligacoes', [RelatorioController::class, 'ligacoes'])->name('relatorios.ligacoes');
Route::get('/relatorios/login', [ReportController::class, 'index'])->name('login-report.index');
Route::get('/relatorios/atividade', [RelatorioController::class, 'index'])->name('relatorios.index');

Route::prefix('filas')->name('filas.')->group(function () {
    Route::get('/', [FilaController::class, 'index'])->name('index');
    Route::get('create', [FilaController::class, 'create'])->name('create');
    Route::post('store', [FilaController::class, 'store'])->name('store');
    Route::get('{id}/edit', [FilaController::class, 'edit'])->name('edit');
    Route::put('update/{id}', [FilaController::class, 'update'])->name('update');
    Route::delete('destroy/{id}', [FilaController::class, 'destroy'])->name('destroy');
    Route::get('{id}/gerenciar', [FilaController::class, 'manageMembers'])->name('manage');
    Route::post('{id}/associar', [FilaController::class, 'associateMember'])->name('associateMember');
    Route::delete('{queueId}/membros/{userId}', [FilaController::class, 'removeMember'])->name('removeMember');
    Route::post('{fila}/member/{member}/update', [FilaController::class, 'updateMemberState'])->name('updateMemberState');
});

//Route::put('/filas/{id}', [FilaController::class, 'update'])->name('filas.update');

Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');
Route::delete('/agents/{id}', [AgentController::class, 'destroy'])->name('agents.destroy');
Route::post('/agents/pause', [AgentController::class, 'pauseAgent'])->name('agents.pause');
Route::get('/agents/test-pause', [AgentController::class, 'pauseTest'])->name('agents.test-pause');
Route::post('/ami', [AgentController::class, 'pauseAgent']);

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
    Route::delete('{troncos}', [RamalController::class, 'destroytronco'])->name('destroy');
});






//Pausas
Route::resource('pauses', PauseController::class);
Route::post('/users/toggle-pause', [UserController::class, 'togglePause'])->name('users.togglePause');
//Route::get('/pauses', [PauseController::class, 'index'])->name('pauses.index'); // Lista de pausas
//Route::post('/pauses', [PauseController::class, 'store'])->name('pauses.store'); // Salvar nova pausa
//Route::get('/pauses/{id}/edit', [PauseController::class, 'edit'])->name('pauses.edit'); // Formulário para editar pausa
//Route::put('/pauses/{id}', [PauseController::class, 'update'])->name('pauses.update'); // Atualizar pausa existente
//Route::delete('/pauses/{id}', [PauseController::class, 'destroy'])->name('pauses.destroy'); // Deletar pausa



// Rota mais específica para retornar todas as pausas
Route::get('/pauses/getAll', [PauseController::class, 'getAll'])->name('pauses.getAll');



Route::get('/teste-pause', [PauseController::class, 'test'])->name('pause.teste');


Route::get('/chama', [PauseController::class, 'chamapausa'])->name('pausa.chama');


Route::post('/pauses/atualizar', [PauseController::class, 'atualizar'])->name('pauses.atualizar');



Route::get('/status', [PauseController::class, 'obterStatus'])->name('status');
Route::post('/finish', [PauseController::class, 'finish'])->name('finish');



    // Retorna a lista de pausas disponíveis
    Route::get('/pauses', [PauseController::class, 'index']);

    // Retorna a lista de pausas disponíveis
    
    Route::get('/Pausas', [PauseController::class, 'showpauses'])->name('Pausas.inicio');
    //Route::get('/Pausas/create', [PauseController::class, 'create'])->name('pauses.create'); // Formulário para criar pausa
    //Route::post('/Pausas/store', [PauseController::class, 'store'])->name('pauses.store'); // Salvar nova pausa
    //Route::get('/pauses/{id}/edit', [PauseController::class, 'edit'])->name('pauses.edit'); // Formulário para editar pausa
    //::put('/pauses/{id}', [PauseController::class, 'update'])->name('pauses.update'); // Atualizar pausa existente

    // Inicia uma pausa
    Route::post('/pauses/start', [PauseController::class, 'startPause']);


    // Encerra uma pausa
    Route::post('/pauses/end', [PauseController::class, 'endPause']);
    Route::post('/pauses/end', [PauseController::class, 'endPause'])->middleware('auth');

   


    Route::get('/user/{userId}/pauses', [PauseController::class, 'getUserPauses']);

   // Defina a rota para o método getLastPause
   Route::get('/pauses/last', [PauseController::class, 'getLastPause'])->name('pauses.last');


//Relatório de pausas
Route::get('/relatorio-pausas', [PauseController::class, 'relatorio'])->name('relatorio.pausas');
Route::post('/relatorio-pausas', [PauseController::class, 'filtrarRelatorio'])->name('relatorio.pausas.filtrar');


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');





Route::post('/campanhas/store', [CampaignController::class, 'store'])->name('campaign.store');

Route::get('/campanhas', [CampaignController::class, 'index'])->name('campaign.index');
Route::get('/campanhas/relatorio', [CampaignController::class, 'report'])->name('report.index');
Route::get('/campanhas/criar', [CampaignController::class, 'criar'])->name('campaign.criar');
Route::get('/campaign/{id}', [CampaignController::class, 'show'])->name('campaign.show');
Route::get('/campaign/{id}/delete', [CampaignController::class, 'delete'])->name('campaign.delete');

Route::post('/campaign/{campaignId}/restart', [CampaignController::class, 'resetCampaign'])->name('campaign.restart');

Route::post('/campaign/{id}/start', [CampaignController::class, 'startCampaign']);

Route::post('/campaign/{campaignId}/stop', [CampaignController::class, 'stopCampaign'])->name('campaign.stop');




Route::get('/campanhas/canais', [CampaignController::class, 'showChannels'])->name('campaign.channels');




Route::post('/save-sippers-data', [MonitorController::class, 'saveSippersData']);


Route::get('/monitoramento', [MonitorController::class, 'index'])->name('monitor.index');

Route::get('/extrato-chamadas/{tipo}', [MonitorController::class, 'getExtratoChamadas']);




Route::get('/Liguetalk', [Liguetalkcontroller::class, 'index'])->name('Liguetalk.index');

Route::post('/glpi/criarticket', [GlpiController::class, 'createTicket'])->name('glpi.createTicket');
Route::get('/glpi/ticket/{id}', [GlpiController::class, 'getTicket']);
Route::get('/glpi/novo-ticket', [GlpiController::class, 'showCreateTicketForm'])->name('glpi.showCreateTicketForm');
Route::get('/glpi/tickets', [GlpiController::class, 'index'])->name('glpi.tickets');

Route::get('/glpi/tickets/filter', [GLPIController::class, 'filterTickets'])->name('glpi.tickets.filter');
Route::put('/tickets/{id}', [GlpiController::class, 'updateTicket'])->name('tickets.update');
Route::delete('/glpi/tickets/{id}', [GlpiController::class, 'deleteTicket'])->name('glpi.tickets.delete');

Route::get('/tickets/{id}/edit', [GlpiController::class, 'edit'])->name('tickets.edit');


//Empresa controller

Route::get('/empresas/create', [UserController::class, 'createEmpresa'])->name('empresas.create');
Route::post('/empresas/store', [UserController::class, 'storeEmpresa'])->name('empresas.store');


Route::get('/tickets/send-summary', [GlpiController::class, 'sendDailyTicketSummary']);


Route::post('/callcreat', [PainelAtendimentoController::class, 'storeCall'])->name('callcreat.store');
Route::get('/calls', [PainelAtendimentoController::class, 'getUserCalls'])->name('calls.getUserCalls');


Route::post('/calls/store', [PainelAtendimentoController::class, 'storeCall'])->name('calls.store');
Route::get('/calls/user', [PainelAtendimentoController::class, 'getUserCalls']);


Route::get('/generate-csrf-token', function() {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::get('/discord/messages', [GlpiController::class, 'getDiscordMessages']);
Route::get('/messages', [GlpiController::class, 'listMessages']);


Route::get('/magnus/saldo', [MagnusController::class, 'getBalance']);
Route::get('/magnus/usuarios', [MagnusController::class, 'getUsers']);

Route::get('/discord/banco', [GlpiController::class, 'getDiscordbanco']);


Route::get('/alldata', [GlpiController::class, 'getAllData']);

Route::get('/process-tickets', [GlpiController::class, 'processAndCreateTickets']);
