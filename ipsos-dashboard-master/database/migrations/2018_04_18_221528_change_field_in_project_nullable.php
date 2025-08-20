<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldInProjectNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('code')->nullable()->change();
            $table->dateTime('start_date')->nullable()->change();
            $table->dateTime('finish_date')->nullable()->change();
            $table->integer('timeline')->default(0)->change();
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
            $table->string('code')->change();
            $table->dateTime('start_date')->change();
            $table->dateTime('finish_date')->change();
            $table->integer('timeline')->change();
        });
    }
}
