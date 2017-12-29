<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PayInterfaceRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_interface_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('bill_id','32')->comment('订单号')->unique();
            $table->unsignedInteger('user_id')->comment('用户id');
            $table->tinyInteger('type')->comment('请求接口类型 1：银行卡鉴权，2：实名认证');
            $table->string('platform','30')->comment('请求的支付平台');
            $table->text('request')->comment('请求数据')->nullable();
            $table->text('response')->comment('响应结果')->nullable();
            $table->tinyInteger('status')->comment('状态 0：未提交，1：已提交未响应，2：处理成功，3：处理失败')->default(0);
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
        Schema::dropIfExists('pay_interface_records');
    }
}
