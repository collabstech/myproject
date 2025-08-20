<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 36);
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('objective')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('finish_date');
            $table->string('respondent')->nullable();
            $table->string('coverage')->nullable();
            $table->string('methodology')->nullable();
            $table->integer('timeline');
            $table->timestamps();
            $table->softDeletes();

            $table->index('start_date', 'start_date_index');
            $table->index('finish_date', 'finish_date_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
