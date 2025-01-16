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


class CampaignController extends Controller
{
    public function index()
    {
        Log::info('Campaign index method called.');
        $campaigns = Campaign::all();
        Log::info('Campaigns retrieved: ', ['count' => $campaigns->count()]);
        return view('campaign.index', compact('campaigns'));
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
            'name' => 'required|string|max:255',
            'context' => 'required|string',
            'extension' => 'required|string',
            'priority' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'numbers_file' => 'required|file|mimes:csv,txt',
            'audio_file' => 'required|file|mimes:wav,mp3',
        ]);

        Log::info('Validação concluída com sucesso.', ['validated_data' => $validated]);

        // Processando as datas
        Log::info('Processando as datas...');
        $startDate = Carbon::parse($validated['start_date'])->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($validated['end_date'])->format('Y-m-d H:i:s');

        // Movendo o arquivo de áudio para o diretório do Asterisk
        $asteriskPath = '/var/lib/asterisk/sounds/custom/';
        $audioFile = $request->file('audio_file');
        $audioFileName = time() . '_' . $audioFile->getClientOriginalName();

        Log::info('Movendo o arquivo de áudio...', [
            'file_name' => $audioFileName,
            'destination' => $asteriskPath,
        ]);

        if (!$audioFile->move($asteriskPath, $audioFileName)) {
            return redirect()->back()->with('error', 'Erro ao salvar o áudio.');
        }

        // Processando o arquivo de números
        Log::info('Processando o arquivo de números...');
        $numbersPath = $request->file('numbers_file')->store('uploads/numbers');
        $numbers = array_map('str_getcsv', file(Storage::path($numbersPath)));

        $validNumbers = [];
        $invalidNumbers = [];

        foreach ($numbers as $line) {
            $phoneNumber = isset($line[0]) ? preg_replace('/[^0-9]/', '', $line[0]) : null; // Remove caracteres não numéricos
            $name = isset($line[1]) ? trim($line[1]) : null;

            if (!empty($phoneNumber) && strlen($phoneNumber) <= 15) {
                $validNumbers[] = [
                    'campaign_id' => null, // Campanha será criada posteriormente
                    'phone_number' => $phoneNumber,
                    'status' => 'pending',
                    'name' => $name,
                ];
            } else {
                $invalidNumbers[] = [
                    'phone_number' => $line[0] ?? 'N/A',
                    'reason' => empty($phoneNumber) ? 'Número vazio ou inválido' : 'Excede 15 caracteres',
                ];
            }
        }

        Log::info('Números processados.', [
            'valid_numbers_count' => count($validNumbers),
            'invalid_numbers_count' => count($invalidNumbers),
        ]);

        // Criando a campanha
        Log::info('Criando a campanha...');
        $campaign = Campaign::create([
            'name' => $validated['name'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'audio_file' => $audioFileName,
            'status' => 'pending',
        ]);

        // Salvando os números válidos
        foreach ($validNumbers as &$validNumber) {
            $validNumber['campaign_id'] = $campaign->id;
            CampaignContact::create($validNumber);
        }

        // Log dos números inválidos (se houver)
        if (!empty($invalidNumbers)) {
            Log::warning('⚠️ Números inválidos encontrados.', [
                'invalid_numbers' => $invalidNumbers,
                'total_invalid' => count($invalidNumbers),
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
    // Conexão com o Asterisk AMI
    $host = "127.0.0.1";
    $port = 5038;
    $username = "admin";
    $password = "MKsx2377!@";
    $context = "testefone";
    $priority = 1;

    try {
        // Conectar ao AMI
        $socket = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$socket) {
            throw new \Exception("Erro ao conectar ao AMI: $errstr ($errno)");
        }

        // Login no AMI
        fputs($socket, "Action: Login\r\nUsername: $username\r\nSecret: $password\r\nEvents: off\r\n\r\n");

        // Buscar contatos e áudio da campanha
        $campaign = Campaign::find($campaignId);
        if (!$campaign) {
            return response()->json(['message' => 'Campanha não encontrada.'], 404);
        }

        $audioFileName = $campaign->audio_file; // Nome do arquivo de áudio
        $contacts = CampaignContact::where('campaign_id', $campaignId)
                                   ->where('status', 'pending')
                                   ->get();

        if ($contacts->isEmpty()) {
            return response()->json(['message' => 'Nenhum contato pendente.'], 404);
        }

        foreach ($contacts as $contact) {
            $callee = $contact->phone_number;
        
            // Extrair o nome do arquivo de áudio sem a extensão
            $audioFileNameWithoutExtension = pathinfo($campaign->audio_file, PATHINFO_FILENAME);
            $audioFilePath = "custom/" . $audioFileNameWithoutExtension; // Prefixa com "custom/"
        
            // Comando Originate para cada número
            $originate = "Action: Originate\r\n" .
                         "Channel: SIP/fonetalk/$callee\r\n" .
                         "Exten: $callee\r\n" .
                         "Context: $context\r\n" .
                         "Priority: $priority\r\n" .
                         "Callerid: \r\n" .
                         "Timeout: 30000\r\n" .
                         "Async: yes\r\n" .
                         "Variable: AUDIO_FILE=$audioFilePath\r\n\r\n";
        
            fputs($socket, $originate);
        
            // Atualiza status no banco
            $contact->update(['status' => 'in_progress']);
        
            usleep(500000); // Pequeno delay entre chamadas
        }

        // Logout do AMI e fechar conexão
        fputs($socket, "Action: Logoff\r\n\r\n");
        fclose($socket);

        return response()->json(['message' => 'Campanha iniciada!']);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



}
