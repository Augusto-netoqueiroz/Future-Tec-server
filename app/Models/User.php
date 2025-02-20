<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cargo',
        'avatar',
        'pause',  // Certifique-se de que o campo 'pause' está presente no $fillable
        'current_pause_log_id',
        'empresa_id',  // Não se esqueça de adicionar o campo empresa_id aqui
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sippeers()
    {
        return $this->hasMany(Sippeer::class, 'id_user', 'id');
    }   

    public function queues()
    {
        return $this->hasMany(Queue::class, 'id_user', 'id');
    }

    public function queueMembers()
    {
        return $this->hasMany(QueueMember::class, 'id_user', 'id');
    }

    public function agent()
    {
        return $this->hasOne(UserAgent::class);
    }

    public function isAdmin()
    {
        return $this->cargo === 'Administrador';
    }

    public function isSupervisor()
    {
        return $this->cargo === 'Supervisor';
    }

    public function isAtendente()
    {
        return $this->cargo === 'Atendente';
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function pauseLogs()
    {
        return $this->hasMany(UserPauseLog::class);
    }

    public function pauses()
{
    return $this->hasMany(UserPauseLog::class);
}


public function empresa()
{
    return $this->belongsTo(Empresa::class);
}




}
