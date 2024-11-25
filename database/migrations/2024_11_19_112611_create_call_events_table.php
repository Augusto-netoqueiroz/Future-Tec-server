<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallEventsTable extends Migration
{
    public function up()
{
    if (!Schema::hasTable('call_events')) {
        Schema::create('call_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('channel');
            $table->text('event_data');
            $table->timestamps();
        });
    }
}

    public function down()
    {
        Schema::dropIfExists('call_events');
    }
}
