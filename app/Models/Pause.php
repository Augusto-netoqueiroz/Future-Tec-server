<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pause extends Model
{
    use HasFactory;

    protected $fillable = ['name']; // Adicione outros campos permitidos para preenchimento aqui


    public function logs()
    {
        return $this->hasMany(UserPauseLog::class);
    }

    // Relacionamento com usuários (se houver uma ligação direta)
public function users()
{
    return $this->belongsToMany(User::class, 'user_pause_logs', 'pause_id', 'user_id');
}



}


