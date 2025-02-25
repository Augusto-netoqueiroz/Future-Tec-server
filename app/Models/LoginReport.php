<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoginReport extends Model
{
    use HasFactory;

    protected $table = 'login_logs';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'ip_address',
        'login_time',
        'logout_time',
        'session_duration'
    ];

    protected $casts = [
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
