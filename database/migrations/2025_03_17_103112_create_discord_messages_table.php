<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('discord_messages', function (Blueprint $table) {
            $table->id();
            $table->string('empresa')->nullable();
            $table->string('protocolo')->nullable();
            $table->string('cliente')->nullable();
            $table->string('cpf')->nullable();
            $table->string('quem_ligou')->nullable();
            $table->text('descricao')->nullable();
            $table->string('categoria')->nullable();
            $table->string('status')->nullable();
            $table->string('att')->nullable();
            $table->string('telefone')->nullable();
            $table->text('endereco')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('discord_messages');
    }
};
