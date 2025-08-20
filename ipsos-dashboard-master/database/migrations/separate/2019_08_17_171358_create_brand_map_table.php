<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrandMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brand_map', function (Blueprint $table) {
            $table->bigInteger('map_id')->unsigned();
            $table->string('brand', 50)->default('');
            $table->double('sales', 8, 2)->default(0.00);
            $table->foreign('map_id')->references('id')->on('maps')->onDelete('cascade');
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
        Schema::dropIfExists('brand_map');
    }
}
