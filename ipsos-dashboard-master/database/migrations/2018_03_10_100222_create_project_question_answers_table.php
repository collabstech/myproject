<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectQuestionAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_question_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->string('code', 50);
            $table->text('answer')->nullable();
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('question_id')->references('id')->on('project_questions');
            $table->index('code');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_question_answers', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['question_id']);
        });
        Schema::dropIfExists('project_question_answers');
    }
}
