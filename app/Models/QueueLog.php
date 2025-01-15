<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueLog extends Model
{
    protected $table = 'queue_log';
    protected $fillable = [
        'time', 'data', 'event_type', 'event', 'duration', 'agent', 'queuename', 'callid',
    ];
    public $timestamps = false;
}
