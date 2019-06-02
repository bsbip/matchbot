<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventInitiationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_initiations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('message_ts');
            $table->dateTime('expire_at')->nullable();
            $table->boolean('start_when_possible')->default(false);
            $table->unsignedInteger('event_id')->nullable();
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
        Schema::dropIfExists('event_initiations');
    }
}
