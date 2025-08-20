<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectResult extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_results', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid', 36);
            $table->integer('project_id')->unsigned();
            $table->dateTime('result_date');
            $table->string('result_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_results', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
        });
        Schema::dropIfExists('project_results');
    }
}
