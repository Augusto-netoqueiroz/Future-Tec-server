<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Atividade extends Model
{
    use HasFactory;

    protected $table = 'atividade'; // Nome da tabela

    protected $fillable = ['user_id', 'acao', 'descricao', 'ip'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
