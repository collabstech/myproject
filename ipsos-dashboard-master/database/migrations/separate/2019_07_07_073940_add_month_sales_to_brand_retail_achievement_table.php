<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMonthSalesToBrandRetailAchievementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brand_retail_achievement', function (Blueprint $table) {
            $table->double('month_sales', 8, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brand_retail_achievement', function (Blueprint $table) {
            $table->dropColumn('month_sales');
        });
    }
}
