<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProgressCqiDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('progress_cqi_datas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('project_id')->unsigned();
            $table->string('main_dealer', 50);
            $table->string('district', 100);
            $table->string('type', 50);
            $table->string('model', 50);
            $table->integer('target')->default(0);
            $table->integer('actual')->default(0);
            $table->timestamps();
        });

        Schema::table('progress_cqi_datas', function(Blueprint $table) {
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
        Schema::dropIfExists('progress_cqi_datas');
    }
}
