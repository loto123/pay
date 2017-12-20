<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->default(0)->index();
            $table->unsignedTinyInteger('oauth_id')->default(0)->commet("0=微信");
            $table->string("appid");
            $table->string("openid")->index();
            $table->boolean('subscribe')->nullable()->default(1);
            $table->string("nickname")->nullable()->default("");
            $table->unsignedInteger('sex')->nullable()->default(0);
            $table->string('language')->nullable()->default("");
            $table->string('city')->nullable()->default("");
            $table->string('province')->nullable()->default("");
            $table->string('country')->nullable()->default("");
            $table->string('headimgurl')->nullable()->default("");
            $table->unsignedInteger('subscribe_time')->nullable()->default(0);
            $table->string("unionid");
            $table->string("remark");
            $table->unsignedInteger("groupid")->nullable()->default(0);
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
        Schema::dropIfExists('oauth_users');
    }
}
