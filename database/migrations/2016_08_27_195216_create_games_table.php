<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->integer('team_id1')->unsigned();
            $table->integer('team_id2')->unsigned();
            $table->integer('turn')->unsigned()->nullable();
            $table->boolean('ended')->default('false');
            $table->boolean('started')->default('false');
            $table->timestamps();

            $table->foreign('team_id1')->references('id')->on('teams');
            $table->foreign('team_id2')->references('id')->on('teams');
            $table->foreign('turn')->references('id')->on('teams');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('games');
    }
}
