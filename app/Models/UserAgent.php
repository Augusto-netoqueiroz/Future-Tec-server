<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAgent extends Model
{
    use HasFactory;

    // Defina a tabela, caso o nome seja diferente de 'user_agents'
    protected $table = 'user_agents'; // Se sua tabela for 'user_agents', essa linha pode ser omitida

    // Permitir atribuição em massa para esses campos
    protected $fillable = ['user_id', 'agent'];

    // Desabilitar timestamps se sua tabela não tiver as colunas created_at e updated_at
    public $timestamps = true; // Se não precisar de timestamps, defina como false

    // Relacionamento com o modelo User
    public function user()
    {
        return $this->belongsTo(User::class); // Relacionamento muitos-para-um com o modelo User
    }
}
