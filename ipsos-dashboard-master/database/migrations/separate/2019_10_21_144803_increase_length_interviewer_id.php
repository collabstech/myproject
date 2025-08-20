<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseLengthInterviewerId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interviewers', function (Blueprint $table) {
            $table->string('interviewer_id', 30)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interviewers', function (Blueprint $table) {
            $table->string('interviewer_id', 10)->change();
        });
    }
}
