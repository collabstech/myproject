<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_projects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('project_id')->unsigned();
            
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_projects', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['project_id']);
        });
        Schema::dropIfExists('company_projects');
    }
}
