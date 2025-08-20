<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSegmentRetailAchievementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segment_retail_achievement', function (Blueprint $table) {
            $table->bigInteger('retail_achievement_id')->unsigned();
            $table->integer('segment_id')->unsigned();
            $table->foreign('retail_achievement_id')->references('id')->on('retail_achievements')->onDelete('cascade');
            $table->foreign('segment_id')->references('id')->on('segments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('segment_retail_achievement');
    }
}
