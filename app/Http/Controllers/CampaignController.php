<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\CampaignContact;

class CampaignController extends Controller
{
    // List all campaigns
    public function index()
    {
        $campaigns = Campaign::all();
        return view('campaigns.index', compact('campaigns'));
    }

    // Show form to create a new campaign
    public function create()
    {
        return view('campaigns.create');
    }

    // Store a new campaign
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Campaign::create($request->only(['name', 'description', 'start_date', 'end_date']));

        return redirect()->route('campaigns.index')->with('success', 'Campaign created successfully.');
    }

    // Show form to upload contacts for a campaign
    public function uploadContacts($id)
    {
        $campaign = Campaign::findOrFail($id);
        return view('campaigns.upload', compact('campaign'));
    }

    // Store uploaded contacts
    public function storeContacts(Request $request, $id)
{
    $request->validate([
        'contacts_file' => 'required|file|mimes:csv,txt',
    ]);

    // Buscar a campanha correspondente
    $campaign = Campaign::findOrFail($id);

    // Abrir o arquivo CSV e processar os contatos
    $file = $request->file('contacts_file');
    $handle = fopen($file->getRealPath(), 'r');
    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
        CampaignContact::create([
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name, // Preencher o nome da campanha
            'phone_number' => $data[0],
            'status' => 'pending',
        ]);
    }
    fclose($handle);

    return redirect()->route('campaigns.index')->with('success', 'Contacts uploaded successfully.');
}

}
