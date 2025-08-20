<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAnswerColumnToResultValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_result_values', function (Blueprint $table) {
            $table->integer('answer_column')->after('answer_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_result_values', function (Blueprint $table) {
            $table->dropColumn('answer_column');
        });
    }
}
