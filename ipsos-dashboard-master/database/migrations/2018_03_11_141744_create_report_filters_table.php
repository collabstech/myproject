<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_filters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->integer('report_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->integer('default_answer')->unsigned()->nullable();
            
            $table->timestamps();

            $table->foreign('project_id')
            ->references('id')
            ->on('projects')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('report_id')
            ->references('id')
            ->on('reports')
            ->onDelete('cascade')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_filters', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['report_id']);
        });
        Schema::dropIfExists('report_filters');
    }
}
