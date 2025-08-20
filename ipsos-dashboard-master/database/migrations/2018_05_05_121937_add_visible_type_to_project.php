<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibleTypeToProject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('visibleTypeTable')->after('timeline')->default(1);
            $table->boolean('visibleTypeBar')->after('visibleTypeTable')->default(1);
            $table->boolean('visibleTypePie')->after('visibleTypeBar')->default(1);
            $table->boolean('visibleTypeLine')->after('visibleTypePie')->default(1);
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
            $table->dropColumn(['visibleTypeTable','visibleTypeBar','visibleTypePie','visibleTypeLine']);
        });
    }
}
