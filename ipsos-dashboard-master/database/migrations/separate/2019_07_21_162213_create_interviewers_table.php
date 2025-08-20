<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterviewersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interviewers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('project_id')->unsigned();
            $table->string('main_dealer_code', 10);
            $table->string('main_dealer_name', 100)->default('');
            $table->string('interviewer_id', 10)->default('');
            $table->integer('achievement')->default(0);
            $table->integer('achievement_percent')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interviewers');
    }
}
