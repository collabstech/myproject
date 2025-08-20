<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMainDealers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_main_dealers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('main_dealer_code', 10);
            $table->integer('user_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('user_main_dealers', function(Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_main_dealers');
    }
}
