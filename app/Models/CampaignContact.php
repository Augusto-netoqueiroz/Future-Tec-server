<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'campaign_id',
        'status',
    ];

    // Define a relação com a campanha
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
