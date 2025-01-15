<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $table = 'queues';
    protected $fillable = [
        'queue_name', 'description', 'name', 'strategy', 'timeout', 'retry',
        'maxlen', 'joinempty', 'musiconhold', 'announce', 'servicelevel', 'weight',
    ];
    public $timestamps = false;
}