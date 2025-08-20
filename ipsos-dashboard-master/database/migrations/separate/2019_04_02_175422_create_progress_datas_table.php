<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProgressDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('progress_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('main_dealer_code', 10);
            $table->string('main_dealer_name', 100);
            $table->string('district', 100);
            $table->string('dealer_code', 20);
            $table->string('dealer_name', 100);
            $table->integer('h1_premium_target');
            $table->integer('h1_premium_achievement');
            $table->integer('h2_premium_target');
            $table->integer('h2_premium_achievement');
            $table->integer('h3_premium_target');
            $table->integer('h3_premium_achievement');
            $table->integer('total_target_premium');
            $table->integer('total_achievement_premium');
            $table->integer('h1_regular_target');
            $table->integer('h1_regular_achievement');
            $table->integer('h2_regular_target');
            $table->integer('h2_regular_achievement');
            $table->integer('h3_regular_target');
            $table->integer('h3_regular_achievement');
            $table->integer('total_target_regular');
            $table->integer('total_achievement_regular');
            $table->integer('h1_total');
            $table->integer('h2_total');
            $table->integer('h3_total');
            $table->integer('total');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('progress_datas', function(Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('progress_datas');
    }
}
