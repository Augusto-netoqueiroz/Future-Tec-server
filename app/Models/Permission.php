<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    // Defina o nome da tabela se for diferente do plural do nome do modelo
    protected $table = 'permissions';

    // Se necessário, defina os campos que podem ser preenchidos
    protected $fillable = ['cargo', 'permissions'];
}
