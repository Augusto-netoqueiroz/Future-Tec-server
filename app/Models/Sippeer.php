<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sippeer extends Model
{
    protected $table = 'sippeers';
    protected $fillable = [
        'name', 'ipaddr', 'modo', 'type', 'user_id', 'user_name',
    ];
}
