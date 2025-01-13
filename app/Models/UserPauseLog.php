<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPauseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pause_name',
        'pause_id',
        'started_at',
        'pause_start',
        'end_at',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pause()
    {
        return $this->belongsTo(Pause::class);
    }
}
