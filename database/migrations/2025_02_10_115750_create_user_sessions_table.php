<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->unique(); // Cada sessão terá um ID único
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('start_time')->useCurrent(); // Horário de início da sessão
            $table->timestamp('end_time')->nullable(); // Horário de término (logout)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_sessions');
    }
};
