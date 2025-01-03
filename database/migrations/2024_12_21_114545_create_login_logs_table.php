<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginLogsTable extends Migration
{
    public function up()
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Referência ao usuário
            $table->ipAddress('ip_address'); // IP do usuário
            $table->timestamp('login_time'); // Hora do login
            $table->timestamp('logout_time')->nullable(); // Hora do logout
            $table->integer('session_duration')->nullable(); // Duração da sessão em segundos
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_logs');
    }
}
