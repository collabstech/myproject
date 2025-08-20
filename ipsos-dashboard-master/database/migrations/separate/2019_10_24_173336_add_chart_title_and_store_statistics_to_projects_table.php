<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChartTitleAndStoreStatisticsToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('chart_titles')->nullable();
            $table->text('store_stat_titles')->nullable();
            $table->text('store_stat_values')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('chart_titles');
            $table->dropColumn('store_stat_titles');
            $table->dropColumn('store_stat_values');
        });
    }
}
