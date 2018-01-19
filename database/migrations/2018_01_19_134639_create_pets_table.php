<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->string("image")->comment("模版文件");
            $table->timestamps();
        });
        Schema::create('pet_parts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("pet_id")->index();
            $table->string("name");
            $table->unsignedSmallInteger("x_index");
            $table->unsignedSmallInteger("y_index");
            $table->unsignedSmallInteger("z_index");
            $table->timestamps();
        });
        Schema::create('pet_part_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("pet_part_id")->index();
            $table->string("name");
            $table->string("image")->comment("素材图片");
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
        Schema::dropIfExists('pets');
        Schema::dropIfExists('pets');
        Schema::dropIfExists('pets');
    }
}
