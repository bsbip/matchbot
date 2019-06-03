<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventInitiationScheduledMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_initiation_scheduled_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('scheduled_message_id');
            $table->string('channel_id');
            $table->unsignedInteger('event_initiation_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_initiation_scheduled_messages');
    }
}
