<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscordMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa', 'protocolo', 'cliente', 'cpf', 'quem_ligou',
        'descricao', 'categoria', 'status', 'att', 'telefone', 'endereco'
    ];
}

