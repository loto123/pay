<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransferRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_record', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transfer_id')->unsigned()->comment('交易ID')->index('transfer_id', 'idx_transfer_id');
            $table->integer('user_id')->unsigned()->comment('交易用户')->index('user_id', 'idx_user_id');
            $table->decimal('amount', 11, 2)->comment('交易金额')->default(0);
            $table->decimal('real_amount', 11, 2)->comment('实际金额')->default(0);
            $table->tinyInteger('stat')->comment('状态 0 未知 1 付钱 2 提钱')->default(0);
            $table->integer('points')->comment('积分')->default(0);
            $table->tinyInteger('mark')->comment('标记 0 未标记 1 已标记')->default(0);
            $table->decimal('fee_amount',11, 2)->comment('手续费金额')->default(0);
            $table->decimal('proxy_percent', 5, 2)->comment('代理分润比例百分比')->default(0);
            $table->integer('pay_transfer_id')->unsigned();
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
        Schema::dropIfExists('transfer_record');
    }
}
