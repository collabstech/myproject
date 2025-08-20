<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectResultValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_result_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('row')->unsigned();
            $table->integer('project_id')->unsigned();
            $table->integer('result_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->integer('answer_id')->unsigned()->nullable();
            $table->text('values')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('question_id');
            $table->index('answer_id');

            $table->foreign('result_id')
            ->references('id')
            ->on('project_results')
            ->onUpdate('cascade')
            ->onDelete('cascade')
            ;
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
            $table->dropIndex(['project_id']);
            $table->dropIndex(['question_id']);
            $table->dropIndex(['answer_id']);
            $table->dropForeign(['result_id']);
        });
        Schema::dropIfExists('project_result_values');
    }
}
