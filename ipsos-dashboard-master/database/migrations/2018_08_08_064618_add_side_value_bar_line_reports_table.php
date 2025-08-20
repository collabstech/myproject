<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSideValueBarLineReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->integer('row_combo2')->after('operation')->default(0)->nullable();
            $table->integer('data_combo2')->after('row_combo2')->default(0)->nullable();
            $table->integer('operation_combo2')->after('data_combo2')->default(0)->nullable();
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
            $table->dropColumn('row_combo2');
            $table->dropColumn('data_combo2');
            $table->dropColumn('operation_combo2');
        });
    }
}
