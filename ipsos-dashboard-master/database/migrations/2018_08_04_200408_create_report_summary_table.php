<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportSummaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->boolean('show_summary')->after('showvalues')->default(0);
        });
        Schema::table('project_questions', function (Blueprint $table) {
            $table->boolean('visibleSummary')->after('visibleFilter')->default(1);
        });
        Schema::create('report_summary', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('report_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('question_id')->references('id')->on('project_questions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('show_summary');
        });
        Schema::table('project_questions', function (Blueprint $table) {
            $table->dropColumn('visibleSummary');
        });
        Schema::table('report_summary', function (Blueprint $table) {
            $table->dropForeign('report_id');
            $table->dropForeign('question_id');
        });
        Schema::dropIfExists('report_summary');
    }
}
