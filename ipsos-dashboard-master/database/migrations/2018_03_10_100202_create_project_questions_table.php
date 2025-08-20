<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->string('code', 50);
            $table->text('question');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
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
        Schema::table('project_questions', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });
        Schema::dropIfExists('project_questions');
    }
}
