<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('需要通知的用户,缺省0：通知所有用户')->default(0)->index();
            $table->char('type',1)->comment('通知类型：1：分润，2：用户注册，3：系统');
            $table->text('content')->comment('消息内容');
            $table->text('title')->comment('消息主题')->nullable();
            $table->string('param',255)->comment('相关参数,type为1时，代表交易id')->nullable();
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
        Schema::dropIfExists('notices');
    }
}
