<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLatLonAddressPhotoToMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->double('lat', 15, 10)->default(0.00);
            $table->double('lon', 15, 10)->default(0.00);
            $table->string('name')->default('');
            $table->text('address');
            $table->string('photo')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn('lat');
            $table->dropColumn('lon');
            $table->dropColumn('name');
            $table->dropColumn('address');
            $table->dropColumn('photo');
        });
    }
}
