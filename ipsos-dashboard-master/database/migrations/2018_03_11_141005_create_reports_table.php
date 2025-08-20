<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 36);
            $table->integer('project_id')->unsigned();
            $table->string('name');
            $table->tinyInteger('type');
            $table->integer('row');
            $table->integer('column');
            $table->integer('data');
            $table->tinyInteger('operation');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')
            ->references('id')
            ->on('projects')
            ->onDelete('cascade')
            ->onUpdate('cascade');
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
            $table->dropForeign(['project_id']);
        });
        Schema::dropIfExists('reports');
    }
}
