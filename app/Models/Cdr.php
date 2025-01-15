<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cdr extends Model
{
    protected $table = 'cdr';
    protected $fillable = [
        'calldate', 'src', 'dst', 'duration', 'billsec', 'disposition', 'uniqueid',
    ];
    public $timestamps = false;
}
