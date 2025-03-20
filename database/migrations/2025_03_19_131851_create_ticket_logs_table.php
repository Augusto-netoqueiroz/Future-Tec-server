<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ticket_logs', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com a tabela 'discord_messages'
            $table->unsignedBigInteger('message_id')->nullable();
            $table->foreign('message_id')
                  ->references('id')
                  ->on('discord_messages')
                  ->onDelete('cascade');
            
            // Mensagem do log
            $table->text('log_message');
            
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('ticket_logs');
    }
}

