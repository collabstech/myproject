<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRetailAchievementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retail_achievements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('project_id')->unsigned();
            $table->string('respondent_id', 20)->default('000000');
            $table->string('province', 50)->default('');
            $table->string('kabupaten', 50)->default('');
            $table->string('kecamatan', 50)->default('');
            $table->string('kelurahan', 50)->default('');
            $table->integer('segment_id')->unsigned();
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
        Schema::dropIfExists('retail_achievements');
    }
}
