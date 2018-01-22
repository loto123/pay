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
            $table->unsignedInteger("user_id");
            $table->string("name")->nullable()->default("");
//            $table->unsignedInteger("type_id")->nullable()->default(0);
            $table->string("hash")->nullable()->default("");
            $table->string("image")->comment("图片")->nullable()->default("");
            $table->unsignedSmallInteger("status")->nullable()->default(0)->comment("状态 ,0=未孵化 1=孵化中 2=已孵化 3=已锁定 4=已删除");
            $table->index(['user_id', 'status']);
            $table->timestamps();
        });
        Schema::create('pet_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("pet_id")->index();
            $table->unsignedInteger("from_user_id")->nullable()->default(0)->comment("转出用户id，0=系统");
            $table->unsignedInteger("to_user_id")->comment("转入用户id")->index();
            $table->unsignedSmallInteger('type')->nullable()->default(0)->comment("0=系统初始赠送，1=交易, 2=交易撤销赠送");
            $table->string("order")->nullable()->default("");
            $table->timestamps();
        });
//        Schema::create('pet_types', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string("name");
//            $table->string("image")->comment("模版文件");
//            $table->timestamps();
//        });
//        Schema::create('pet_parts', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string("name");
//            $table->unsignedInteger("pet_id")->index();
//            $table->unsignedSmallInteger("x_index");
//            $table->unsignedSmallInteger("y_index");
//            $table->unsignedSmallInteger("z_index");
//            $table->timestamps();
//        });
//        Schema::create('pet_part_items', function (Blueprint $table) {
//            $table->increments('id');
//            $table->string("name");
//            $table->unsignedInteger("pet_part_id")->index();
//            $table->string("image")->comment("素材图片");
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pets');
        Schema::dropIfExists('pet_records');
//        Schema::dropIfExists('pet_types');
//        Schema::dropIfExists('pet_parts');
//        Schema::dropIfExists('pet_part_items');
    }
}
