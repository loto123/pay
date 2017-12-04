<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id')->unsigned()->comment('交易所属店铺ID（群ID）')->index('shop_id', 'idx_shop_id');
            $table->integer('user_id')->unsigned()->comment('发起交易用户')->index('user_id', 'idx_user_id');
            $table->decimal('price', 6, 1)->comment('单价');
            $table->decimal('amount', 11, 2)->comment('红包余额')->default(0);
            $table->string('comment', 500)->comment('备注')->default('');
            $table->tinyInteger('status')->comment('状态 0 未知 1 待结算 2 已平账 3 已关闭')->default(1);
            $table->tinyInteger('tip_type')->comment('茶水费类型 0 未知 1 大赢家抽成 2 普通抽成');
            $table->smallInteger('tip_percent')->comment('茶水费比例')->default(0);
            $table->decimal('tip_amount',11, 2)->comment('茶水费金额')->default(0);
            $table->smallInteger('fee_percent')->comment('手续费比例')->default(0);
            $table->decimal('fee_amount',11, 2)->comment('手续费金额')->default(0);
            $table->tinyInteger('tip_status')->comment('茶水费状态 0 未付 1 已付')->default(0);
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
        Schema::dropIfExists('transfer');
    }
}
