<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginReport extends Model
{
    use HasFactory;

    // Defina a tabela, se o nome da tabela não seguir o padrão
    protected $table = 'login_logs'; // Substitua pelo nome correto da sua tabela

    // Relacionamento com o modelo User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // Substitua 'user_id' pela chave estrangeira correta
    }
}
