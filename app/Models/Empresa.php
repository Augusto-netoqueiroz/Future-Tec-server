<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    protected $table = 'empresas';
    // Definindo os campos preenchíveis
    protected $fillable = [
        'nome', 
        'cnpj',
    ];

    // Definindo os campos de data para que sejam tratados como instâncias de Carbon
    protected $dates = ['created_at', 'updated_at'];



public function store(Request $request)
{
    // Validando o campo CNPJ para garantir que ele seja único
    $request->validate([
        'nome' => 'required|string|max:255',
        'cnpj' => 'required|string|max:255|unique:empresas,cnpj',  // Certifique-se de usar 'empresas' no nome da tabela
    ]);

    // Criando uma nova empresa com os dados validados
    Empresa::create($request->all());

    return redirect()->route('empresas.index');  // Redireciona após salvar
}

}