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

    public function create()
    {
        Log::info('Campaign create method called.');
        return view('campaign.create');
    }
    

public function store(Request $request)
{
    Log::info('Iniciando o processo de criação de campanha', ['input_data' => $request->all()]);

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

    Log::info('Dados validados com sucesso', ['validated_data' => $validated]);

    try {
        // Convertendo as datas para o formato adequado para o banco de dados
        $startDate = Carbon::parse($validated['start_date'])->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($validated['end_date'])->format('Y-m-d H:i:s');
        
        Log::info('Datas convertidas para o formato correto', ['start_date' => $startDate, 'end_date' => $endDate]);

        // Salvar os arquivos no storage local
        $numbersPath = $request->file('numbers_file')->store('uploads/numbers');
        $audioPath = $request->file('audio_file')->store('uploads/audio');

        Log::info('Arquivos armazenados com sucesso', ['numbers_file' => basename($numbersPath), 'audio_file' => basename($audioPath)]);

        // Criar a campanha
        $campaign = Campaign::create([
            'name' => $validated['name'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'audio_file' => basename($audioPath),
            'status' => 'pending',
        ]);

        Log::info('Campanha criada com sucesso', ['campaign_id' => $campaign->id]);


// Processar os números do arquivo e salvar na tabela campaign_contacts
        $numbers = array_map('str_getcsv', file(Storage::path($numbersPath)));

        foreach ($numbers as $number) {
            // Salvando o número na tabela campaign_contacts
            CampaignContact::create([
                'campaign_id' => $campaign->id,
                'phone_number' => trim($number[0]), // Considerando que o número está na primeira coluna
                'status' => 'pending',
                'name' => isset($number[1]) ? trim($number[1]) : null, // Adicionando nome, se presente
            ]);
        }

        Log::info('Números adicionados à campanha', ['numbers_count' => count($numbers)]);


        


        return redirect()->route('campaign.index')->with('success', 'Campanha criada com sucesso!');
    } catch (\Exception $e) {
        Log::error('Erro ao criar a campanha', ['error_message' => $e->getMessage()]);
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
        $context = "rotapentagono";
        $priority = 1;
    
        try {
            // Conectar ao AMI
            $socket = fsockopen($host, $port, $errno, $errstr, 10);
            if (!$socket) {
                throw new \Exception("Erro ao conectar ao AMI: $errstr ($errno)");
            }
    
            // Login no AMI
            fputs($socket, "Action: Login\r\nUsername: $username\r\nSecret: $password\r\nEvents: off\r\n\r\n");
    
            // Buscar contatos da campanha
            $contacts = CampaignContact::where('campaign_id', $campaignId)
                                       ->where('status', 'pending')
                                       ->get();
    
            if ($contacts->isEmpty()) {
                return response()->json(['message' => 'Nenhum contato pendente.'], 404);
            }
    
            foreach ($contacts as $contact) {
                $callee = $contact->phone_number;
    
                // Comando Originate para cada número
                $originate = "Action: Originate\r\n" .
                             "Channel: SIP/Pentagono/$callee\r\n" .
                             "Exten: $callee\r\n" .
                             "Context: $context\r\n" .
                             "Priority: $priority\r\n" .
                             "Callerid: \r\n" .
                             "Timeout: 30000\r\n" .
                             "Async: yes\r\n\r\n";
    
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
