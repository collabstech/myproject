<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalColumnForCsiToProgressDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('progress_datas', function (Blueprint $table) {
            $table->integer('h3_total_achievement')->default(0)->after('total_achievement_regular');
            $table->integer('h2_total_achievement')->default(0)->after('total_achievement_regular');
            $table->integer('h1_total_achievement')->default(0)->after('total_achievement_regular');
            $table->integer('total_achievement')->default(0)->after('total_achievement_regular');
            $table->string('brand', 100)->default('')->after('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('progress_datas', function (Blueprint $table) {
            $table->dropColumn('h3_total_achievement');
            $table->dropColumn('h2_total_achievement');
            $table->dropColumn('h1_total_achievement');
            $table->dropColumn('total_achievement');
            $table->dropColumn('brand');
        });
    }
}
