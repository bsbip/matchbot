<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventInitiationIdIndexToEventInitiationUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_initiation_users', function (Blueprint $table) {
            $table->index('event_initiation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_initiation_users', function (Blueprint $table) {
            $table->dropIndex('event_initiation_users_event_initiation_id_index');
        });
    }
}
