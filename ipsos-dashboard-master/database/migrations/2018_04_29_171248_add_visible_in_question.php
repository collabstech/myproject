<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibleInQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_questions', function (Blueprint $table) {
            $table->boolean('visibleTop')->after('question_alias')->default(1);
            $table->boolean('visibleSide')->after('visibleTop')->default(1);
            $table->boolean('visibleValue')->after('visibleSide')->default(1);
            $table->boolean('visibleFilter')->after('visibleValue')->default(1);
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
            $table->dropColumn(['visibleTop', 'visibleSide', 'visibleValue', 'visibleFilter']);
        });
    }
}
