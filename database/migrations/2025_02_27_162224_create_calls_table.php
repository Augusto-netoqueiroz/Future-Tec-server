<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('calls', function (Blueprint $table) {
        $table->id();
        $table->string('user_name');
        $table->string('ramal');
        $table->string('calling_to');
        $table->string('queue_name')->nullable();
        $table->string('call_duration')->default('00:00');
        $table->string('channel');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
