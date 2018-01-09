<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('platform')->comment('平台,0=ios 1=android')->default(0);
            $table->string('ver_name');
            $table->unsignedSmallInteger('ver_code');
            $table->string("url");
            $table->text('changelog');
            $table->timestamps();
            $table->index(['platform', 'ver_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('versions');
    }
}
