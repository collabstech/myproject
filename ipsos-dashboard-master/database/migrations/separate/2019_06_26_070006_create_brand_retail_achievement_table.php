<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrandRetailAchievementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brand_retail_achievement', function (Blueprint $table) {
            $table->bigInteger('retail_achievement_id')->unsigned();
            $table->integer('brand_id')->unsigned();
            $table->foreign('retail_achievement_id')->references('id')->on('retail_achievements')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brand_retail_achievement');
    }
}
