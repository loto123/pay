<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('index_modules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger("module_id");
            $table->unsignedSmallInteger("type")->default(0)->index();
            $table->unsignedSmallInteger("order")->default(0)->index();
            $table->string("name");
            $table->string("logo");
            $table->string("url");
            $table->timestamps();
        });
        Schema::create('index_module_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger("module_id");
            $table->unsignedSmallInteger("role_id");
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
        Schema::dropIfExists('index_modules');
        Schema::dropIfExists('index_module_roles');
    }
}
