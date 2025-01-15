<?php

 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgenteRamalVinculo extends Model
{
    protected $table = 'agente_ramal_vinculo'; // Verifique se o nome da tabela está correto

    protected $fillable = [
        'agente_id', // ID do usuário
        'ramal_id',  // ID do ramal
        'inicio_vinculo',
        'fim_vinculo',
    ];

    /**
     * Relacionamento com o modelo User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'agente_id'); // agente_id é a chave estrangeira
    }

    /**
     * Relacionamento com o modelo Sippeer.
     */
    public function sippeer()
    {
        return $this->belongsTo(Sippeer::class, 'ramal_id'); // ramal_id é a chave estrangeira
    }
}
