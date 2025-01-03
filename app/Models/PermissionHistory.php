<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionHistory extends Model
{
    use HasFactory;

    protected $table = 'permission_histories'; // Nome da tabela
    // Defina os campos que podem ser preenchidos (fillables) se necessário
}
