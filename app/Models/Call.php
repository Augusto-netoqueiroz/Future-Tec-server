<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{

    protected $fillable = ['user_name', 'ramal', 'calling_to', 'queue_name', 'call_duration', 'channel', 'protocolo'];
}

