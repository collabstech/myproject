<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRetailProgressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retail_progresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('project_id')->unsigned();
            $table->string('sample_id', 20)->default('0000000000');
            $table->string('province', 50)->default('');
            $table->string('kabupaten', 50)->default('');
            $table->string('kecamatan', 50)->default('');
            $table->string('kelurahan', 50)->default('');
            $table->smallInteger('weeks')->default(0);
            $table->integer('number_of_interview')->default(0);
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('retail_progresses');
    }
}
