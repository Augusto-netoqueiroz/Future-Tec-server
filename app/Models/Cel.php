<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cel extends Model
{
    protected $table = 'cel';
    protected $fillable = [
        'eventtype', 'eventtime', 'cid_name', 'cid_num', 'exten', 'context',
        'uniqueid', 'linkedid', 'peer', 'application', 'appdata',
    ];
    public $timestamps = false;
}
