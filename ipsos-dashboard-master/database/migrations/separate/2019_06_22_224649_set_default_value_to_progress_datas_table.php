<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetDefaultValueToProgressDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('progress_datas', function (Blueprint $table) {
            $table->string('main_dealer_code', 10)->default('')->change();
            $table->string('main_dealer_name', 100)->default('')->change();
            $table->string('district', 100)->default('')->change();
            $table->string('dealer_code', 20)->default('')->change();
            $table->string('dealer_name', 100)->default('')->change();
            $table->integer('h1_premium_target')->default(0)->change();
            $table->integer('h1_premium_achievement')->default(0)->change();
            $table->integer('h2_premium_target')->default(0)->change();
            $table->integer('h2_premium_achievement')->default(0)->change();
            $table->integer('h3_premium_target')->default(0)->change();
            $table->integer('h3_premium_achievement')->default(0)->change();
            $table->integer('total_target_premium')->default(0)->change();
            $table->integer('total_achievement_premium')->default(0)->change();
            $table->integer('h1_regular_target')->default(0)->change();
            $table->integer('h1_regular_achievement')->default(0)->change();
            $table->integer('h2_regular_target')->default(0)->change();
            $table->integer('h2_regular_achievement')->default(0)->change();
            $table->integer('h3_regular_target')->default(0)->change();
            $table->integer('h3_regular_achievement')->default(0)->change();
            $table->integer('total_target_regular')->default(0)->change();
            $table->integer('total_achievement_regular')->default(0)->change();
            $table->integer('h1_total')->default(0)->change();
            $table->integer('h2_total')->default(0)->change();
            $table->integer('h3_total')->default(0)->change();
            $table->integer('total')->default(0)->change();
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
            //
        });
    }
}
