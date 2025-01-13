<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PauseLog extends Model
{
    protected $fillable = ['user_id', 'pause_id', 'start_time', 'end_time'];

    public function pause()
    {
        return $this->belongsTo(Pause::class, 'pause_id');
    }
}
