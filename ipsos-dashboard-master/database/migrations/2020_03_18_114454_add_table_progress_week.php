<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableProgressWeek extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('progress_weeks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('main_dealer_code');
            $table->string('main_dealer_name');
            $table->integer('week')->unsigned();
            $table->string('date');
            $table->integer('target');
            $table->integer('achievement');
            $table->integer('achievement_percent');
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
        Schema::dropIfExists('progress_weeks');
    }
}
