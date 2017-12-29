<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('mobile')->unique();
            $table->string('password');
            $table->string('avatar');
            $table->tinyInteger('status')->comment('状态 0：正常 1：冻结')->default(0);
            $table->integer('pay_card_id')->comment('结算卡id')->nullable();
            $table->string('pay_password')->comment('支付密码')->nullable();
            $table->tinyInteger('identify_status')->comment('实名认证 1：已认证，0：未认证')->default(0);
            $table->char('id_number','18')->comment('身份证号')->nullable();
            $table->unsignedInteger('parent_id')->nullable()->default(0);
            $table->unsignedInteger('operator_id')->nullable()->default(0);
            $table->unsignedInteger('container_id');
            $table->unsignedSmallInteger('channel_id')->nullable()->default(0);
            $table->decimal("percent", 5)->comment("代理分成比例");
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
