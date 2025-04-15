<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Campaign;
use App\Models\CampaignContact;
use Carbon\Carbon;
use App\Services\AmiService;


class CampaignController extends Controller
{
    // app/Http/Controllers/CampaignController.php

 

    public function index()
    {
        $campaigns = Campaign::paginate(10); // Paginação com 10 itens por página
        Log::info('Campaign index method called.');
        Log::info('Campaigns retrieved: ', ['count' => $campaigns->count()]);
    
        // Para cada campanha, calcula as métricas
        foreach ($campaigns as $campaign) {
            // Busca os contatos associados à campanha
            $contacts = DB::table('campaign_contacts')
                ->where('campaign_id', $campaign->id)
                ->get();
    
            $total = $contacts->count();
            $concluidos = 0;
            $atendidos = 0;
    
            foreach ($contacts as $contact) {
                // Monta o padrão conforme: idContato - nomeCampanha
                $pattern = $contact->id . '-' . $campaign->name;
    
                // Busca apenas o último registro (status final) na tabela cdr para o padrão encontrado
                $lastCall = DB::table('cdr')
                    ->where('userfield', $pattern)
                    ->orderBy('calldate', 'desc')
                    ->first();
    
                if ($lastCall) {
                    $concluidos++;  // Conta apenas o último registro como status final
    
                    // Considera a ligação atendida quando disposition for 'ANSWERED'
                    if ($lastCall->disposition === 'ANSWERED') {
                        $atendidos++;
                    }
                }
            }
    
            // Atribui os valores calculados à instância da campanha
            $campaign->total = $total;
            $campaign->concluidos = $concluidos;
            $campaign->atendidos = $atendidos;
            $campaign->nao_atendidos = $concluidos - $atendidos;
        }
    
        return view('campaign.index', compact('campaigns'));
    }
    



    public function report()
    {
        $reportData = DB::table('campaigns')
            ->select('campaigns.id', 'campaigns.name', 'campaigns.status', 'campaigns.start_date', 'campaigns.end_date',
                DB::raw('COUNT(campaign_contacts.id) as total_contacts'),
                DB::raw('SUM(CASE WHEN campaign_contacts.status = "completed" THEN 1 ELSE 0 END) as completed_contacts'),
                DB::raw('SUM(CASE WHEN campaign_contacts.status = "pending" THEN 1 ELSE 0 END) as pending_contacts'))
            ->leftJoin('campaign_contacts', 'campaigns.id', '=', 'campaign_contacts.campaign_id')
            ->groupBy('campaigns.id', 'campaigns.name', 'campaigns.status', 'campaigns.start_date', 'campaigns.end_date')
            ->orderBy('campaigns.start_date', 'desc')
            ->get();

        return view('campaign.report', compact('reportData'));
    }




    public function criar()
    {
        
        Log::info('Função criar foi chamada.', ['url' => request()->url(), 'method' => request()->method()]);
        return view('campaign.criar');
    }
    

    public function store(Request $request)
{
    Log::info('Função store foi chamada.', ['url' => request()->url(), 'method' => request()->method()]);

    try {
        // Validação dos dados do formulário
        Log::info('Iniciando validação dos dados...');
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'context'     => 'required|string',
            'extension'   => 'required|string',
            'priority'    => 'required|integer',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'numbers_file'=> 'required|file|mimes:csv,txt',
            'audio_file'  => 'required|file|mimes:wav,mp3',
            // Adicione a validação de batch_size (1 a 100)
            'batch_size'  => 'required|integer|min:1|max:100',
        ]);

        Log::info('Validação concluída com sucesso.', ['validated_data' => $validated]);

        // Processando as datas
        Log::info('Processando as datas...');
        $startDate = Carbon::parse($validated['start_date'])->format('Y-m-d H:i:s');
        $endDate   = Carbon::parse($validated['end_date'])->format('Y-m-d H:i:s');

        // Movendo o arquivo de áudio para o diretório do Asterisk
        $asteriskPath   = '/var/lib/asterisk/sounds/custom/';
        $audioFile      = $request->file('audio_file');
        $audioFileName  = time() . '_' . $audioFile->getClientOriginalName();

        Log::info('Movendo o arquivo de áudio...', [
            'file_name'   => $audioFileName,
            'destination' => $asteriskPath,
        ]);

        if (!$audioFile->move($asteriskPath, $audioFileName)) {
            return redirect()->back()->with('error', 'Erro ao salvar o áudio.');
        }

        // Processando o arquivo de números
        Log::info('Processando o arquivo de números...');
        $numbersPath = $request->file('numbers_file')->store('uploads/numbers');
        $numbers     = array_map('str_getcsv', file(Storage::path($numbersPath)));

        $validNumbers   = [];
        $invalidNumbers = [];

        foreach ($numbers as $line) {
            $phoneNumber = isset($line[0]) ? preg_replace('/[^0-9]/', '', $line[0]) : null; // Remove caracteres não numéricos
            $name        = isset($line[1]) ? trim($line[1]) : null;

            if (!empty($phoneNumber) && strlen($phoneNumber) <= 15) {
                $validNumbers[] = [
                    'campaign_id'  => null, // Campanha será criada posteriormente
                    'phone_number' => $phoneNumber,
                    'status'       => 'pending',
                    'name'         => $name,
                ];
            } else {
                $invalidNumbers[] = [
                    'phone_number' => $line[0] ?? 'N/A',
                    'reason'       => empty($phoneNumber) ? 'Número vazio ou inválido' : 'Excede 15 caracteres',
                ];
            }
        }

        Log::info('Números processados.', [
            'valid_numbers_count'   => count($validNumbers),
            'invalid_numbers_count' => count($invalidNumbers),
        ]);

        // Criando a campanha
        Log::info('Criando a campanha...');
        $campaign = Campaign::create([
            'name'       => $validated['name'],
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'audio_file' => $audioFileName,
            'status'     => 'pending',
            // Salva o batch_size informado pelo usuário
            'batch_size' => $validated['batch_size'],
        ]);

        // Salvando os números válidos
        foreach ($validNumbers as &$validNumber) {
            $validNumber['campaign_id'] = $campaign->id;
            CampaignContact::create($validNumber);
        }

        Log::info('Inserindo números válidos...', ['valid_numbers' => $validNumbers]);

        // Log dos números inválidos (se houver)
        if (!empty($invalidNumbers)) {
            Log::warning('⚠️ Números inválidos encontrados.', [
                'invalid_numbers' => $invalidNumbers,
                'total_invalid'   => count($invalidNumbers),
            ]);
        }

        // Adicionando números inválidos na sessão para exibição
        session()->flash('invalid_numbers', $invalidNumbers);

        Log::info('Campanha criada com sucesso!', ['campaign_id' => $campaign->id]);

        return redirect()->route('campaign.index')->with('success', 'Campanha criada com sucesso!');
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Erros de validação
        Log::error('Erros de validação encontrados.', ['errors' => $e->errors()]);
        return redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        // Erros gerais
        Log::error('Erro ao criar a campanha.', ['error_message' => $e->getMessage()]);
        return redirect()->back()->with('error', 'Erro ao criar a campanha: ' . $e->getMessage());
    }
}


    public function show($id)
    {
        Log::info('Campaign show method called.', ['id' => $id]);
        $campaign = Campaign::findOrFail($id);
        return view('campaign.show', compact('campaign'));
    }

    public function delete($id)
    {
        Log::info('Campaign delete method called.', ['id' => $id]);
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();
        Log::info('Campaign deleted.', ['id' => $id]);

        return redirect()->route('campaign.index')->with('success', 'Campanha excluída com sucesso!');
    }

    public function startCampaign($campaignId)
{
    // Configurações do Asterisk
    $host = "127.0.0.1";
    $port = 5038;
    $username = "admin";
    $password = "MKsx2377!@";
    $context = "testefone";
    $priority = 1;

    Log::info("Iniciando campanha com ID: {$campaignId}");

    try {
        // Buscar campanha no banco
        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            Log::error("Campanha não encontrada: ID {$campaignId}");
            return response()->json(['message' => 'Campanha não encontrada.'], 404);
        }

        Log::info("Campanha encontrada: {$campaign->name}");

        // Verificar horários permitidos
        $currentDateTime = Carbon::now();
        if (
            $currentDateTime->lt(Carbon::parse($campaign->start_date)) ||
            $currentDateTime->gt(Carbon::parse($campaign->end_date))
        ) {
            Log::warning("Campanha fora do horário permitido. Início: {$campaign->start_date}, Fim: {$campaign->end_date}, Agora: {$currentDateTime}");
            return response()->json(['message' => 'Campanha não pode ser iniciada fora do horário configurado.'], 403);
        }

        Log::info("Horário permitido validado. Atualizando status da campanha para 'in_progress'.");

        // Atualizar status para "in_progress"
        $campaign->update(['status' => 'in_progress']);

        // Mensagem de sucesso inicial
        Log::info("Campanha ID {$campaignId} iniciada com sucesso.");
        echo json_encode(['message' => 'Campanha iniciada com sucesso!']);

        // Conectar ao Asterisk AMI
        Log::info("Conectando ao Asterisk AMI...");
        $socket = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$socket) {
            Log::error("Erro ao conectar ao AMI: $errstr ($errno)");
            throw new \Exception("Erro ao conectar ao AMI: $errstr ($errno)");
        }

        Log::info("Conexão ao Asterisk AMI estabelecida com sucesso.");

        // Fazer login no Asterisk AMI
        fputs($socket, "Action: Login\r\nUsername: $username\r\nSecret: $password\r\nEvents: on\r\n\r\n");

        // Recuperar o batch_size definido na campanha
        $batchSize = $campaign->batch_size ?? 5;  // Se não vier, assume 5, por segurança

        // Processar contatos em lotes de $batchSize
        while (true) {
            Log::info("Buscando até {$batchSize} contatos pendentes para a campanha ID {$campaignId}...");
            $contacts = CampaignContact::where('campaign_id', $campaignId)
                                       ->where('status', 'pending')
                                       ->take($batchSize) // Substituiu o fixo '30'
                                       ->get();

            if ($contacts->isEmpty()) {
                Log::info("Nenhum contato pendente encontrado. Finalizando campanha ID {$campaignId}.");

                // Atualizar status para "stopped"
                $campaign->update(['status' => 'stopped']);

                // Mensagem de finalização
                echo json_encode(['message' => 'Campanha finalizada com sucesso!']);
                break;
            }

            Log::info(count($contacts) . " contatos pendentes encontrados para processamento.");

            foreach ($contacts as $contact) {
                $callee = $contact->phone_number;
                $audioFilePath = "custom/" . pathinfo($campaign->audio_file, PATHINFO_FILENAME);

                // Criando a variável CAMPAIGN_NAME com ID do contato e nome da campanha
                $campaignNameFormatted = "{$contact->id}-{$campaign->name}";

                $originate = "Action: Originate\r\n" .
                             "Channel: SIP/fonetalk/$callee\r\n" .
                             "Exten: $callee\r\n" .
                             "Context: $context\r\n" .
                             "Priority: $priority\r\n" .
                             "Callerid: \r\n" .
                             "Timeout: 30000\r\n" .
                             "Async: yes\r\n" .
                             "Variable: AUDIO_FILE=$audioFilePath\r\n" .
                             "Variable: CAMPAIGN_NAME={$campaignNameFormatted}\r\n\r\n";

                fputs($socket, $originate);

                // Atualizar status do contato para "in_progress"
                $contact->update(['status' => 'in_progress']);
            }

            Log::info("Aguardando 30 segundos antes de processar o próximo lote de contatos...");
            sleep(30); 
        }

        // Fazer logoff do AMI
        Log::info("Finalizando conexão com o Asterisk AMI...");
        fputs($socket, "Action: Logoff\r\n\r\n");
        fclose($socket);

    } catch (\Exception $e) {
        Log::error("Erro ao processar campanha ID {$campaignId}: " . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


 

    public function stopCampaign($campaignId)
{
    try {
        // Encontrar a campanha
        $campaign = Campaign::findOrFail($campaignId);

        // Verificar se a campanha está em progresso
        if ($campaign->status !== 'in_progress') {
            return response()->json(['message' => 'A campanha não está em progresso.'], 400);
        }

        // Atualizar o status da campanha para 'stopped'
        $campaign->status = 'stopped';
        $campaign->save();

        // Atualizar o status dos contatos associados para 'paused'
        CampaignContact::where('campaign_id', $campaignId)
            ->whereIn('status', ['in_progress', 'pending']) // Atualiza os contatos com status 'in_progress' e 'pending'
            ->update(['status' => 'paused']);

        return response()->json(['message' => 'Campanha parada com sucesso.']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao parar a campanha: ' . $e->getMessage()], 500);
    }
}


public function resetCampaign($campaignId)
{
    try {
        // Encontrar a campanha
        $campaign = Campaign::findOrFail($campaignId);

        // Verificar se a campanha está com status 'stopped'
        if ($campaign->status !== 'stopped') {
            return response()->json(['message' => 'A campanha não está parada. Apenas campanhas paradas podem ser redefinidas.'], 400);
        }

        // Atualizar o status da campanha para 'pending'
        $campaign->status = 'pending';
        $campaign->save();

        // Atualizar o status dos contatos para 'pending'
        CampaignContact::where('campaign_id', $campaignId)
            ->whereIn('status', ['paused', 'in_progress'])
            ->update(['status' => 'pending']);

        return response()->json(['message' => 'Campanha redefinida com sucesso.']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao redefinir a campanha: ' . $e->getMessage()], 500);
    }
}

public function updateStatusFromCdr()
    {
        Log::info('Atualizando status dos contatos a partir da tabela CDR.');

        $contacts = CampaignContact::whereIn('status', ['pending', 'in_progress'])->get();
        
        foreach ($contacts as $contact) {
            $cdrEntry = DB::table('cdr')
                ->where('userfield', 'LIKE', "%{$contact->campaign_id}-{$contact->id}%")
                ->orderBy('calldate', 'desc')
                ->first();

            if ($cdrEntry) {
                if ($cdrEntry->disposition === 'ANSWERED') {
                    if (strpos($cdrEntry->lastapp, 'Playback') !== false) {
                        $contact->status = 'heard_audio';
                    } else {
                        $contact->status = 'answered';
                    }
                } elseif ($cdrEntry->disposition === 'NO ANSWER') {
                    $contact->status = 'no_answer';
                } elseif ($cdrEntry->disposition === 'BUSY') {
                    $contact->status = 'busy';
                } else {
                    $contact->status = 'failed';
                }
                
                $contact->save();
                Log::info("Contato atualizado: {$contact->id} - Status: {$contact->status}");
            }
        }
    }



    public function showChannels()
    {
       
            return view('campaign.channels');
       
    }
    
    
   

public function getMetricsAttribute()
{
    // Busca os contatos associados à campanha
    $contacts = \DB::table('campains_contats')
        ->where('campaign_id', $this->id)
        ->get();

    $total = $contacts->count();
    $concluidos = 0;
    $atendidos = 0;

    foreach ($contacts as $contact) {
        // Monta o padrão conforme o formato definido (id-nomeDaCampanha)
        $pattern = $contact->id . '-' . $this->name;
        $calls = \DB::table('cdr')
            ->where('userfield', $pattern)
            ->get();

        $concluidos += $calls->count();
        // Supondo que 'ANSWERED' define a ligação atendida.
        $atendidos += $calls->where('disposition', 'ANSWERED')->count();
    }

    $nao_atendidos = $concluidos - $atendidos;

    return [
        'total'          => $total,
        'concluidos'     => $concluidos,
        'atendidos'      => $atendidos,
        'nao_atendidos'  => $nao_atendidos
    ];
}
 

}
