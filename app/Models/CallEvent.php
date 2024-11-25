<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallEvent extends Model
{
    use HasFactory;

    protected $table = 'call_events';

    protected $fillable = [
        'event_type', 
        'channel', 
        'event_data',
    ];

    public $timestamps = true;
}
